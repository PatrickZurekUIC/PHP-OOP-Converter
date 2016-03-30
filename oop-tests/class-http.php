<?php

class WP_Http {

	public function request( $url, $args = array() ) {
		global $wp_version;

		$defaults = array(
			'method' => 'GET',
			/**
			 * Filter the timeout value for an HTTP request.
			 *
			 * @since 2.7.0
			 *
			 * @param int $timeout_value Time in seconds until a request times out.
			 *                           Default 5.
			 */
			'timeout' => apply_filters( 'http_request_timeout', 5 ),
			/**
			 * Filter the number of redirects allowed during an HTTP request.
			 *
			 * @since 2.7.0
			 *
			 * @param int $redirect_count Number of redirects allowed. Default 5.
			 */
			'redirection' => apply_filters( 'http_request_redirection_count', 5 ),
			/**
			 * Filter the version of the HTTP protocol used in a request.
			 *
			 * @since 2.7.0
			 *
			 * @param string $version Version of HTTP used. Accepts '1.0' and '1.1'.
			 *                        Default '1.0'.
			 */
			'httpversion' => apply_filters( 'http_request_version', '1.0' ),
			/**
			 * Filter the user agent value sent with an HTTP request.
			 *
			 * @since 2.7.0
			 *
			 * @param string $user_agent WordPress user agent string.
			 */
			'user-agent' => apply_filters( 'http_headers_useragent', 'WordPress/' . $wp_version . '; ' . get_bloginfo( 'url' ) ),
			/**
			 * Filter whether to pass URLs through wp_http_validate_url() in an HTTP request.
			 *
			 * @since 3.6.0
			 *
			 * @param bool $pass_url Whether to pass URLs through wp_http_validate_url().
			 *                       Default false.
			 */
			'reject_unsafe_urls' => apply_filters( 'http_request_reject_unsafe_urls', false ),
			'blocking' => true,
			'headers' => array(),
			'cookies' => array(),
			'body' => null,
			'compress' => false,
			'decompress' => true,
			'sslverify' => true,
			'sslcertificates' => ABSPATH . WPINC . '/certificates/ca-bundle.crt',
			'stream' => false,
			'filename' => null,
			'limit_response_size' => null,
		);

		// Pre-parse for the HEAD checks.
		$args = wp_parse_args( $args );

		// By default, Head requests do not cause redirections.
		if ( isset($args['method']) && 'HEAD' == $args['method'] )
			$defaults['redirection'] = 0;

		$r = wp_parse_args( $args, $defaults );
		$r = apply_filters( 'http_request_args', $r, $url );

		// The transports decrement this, store a copy of the original value for loop purposes.
		if ( ! isset( $r['_redirection'] ) )
			$r['_redirection'] = $r['redirection'];

		$pre = apply_filters( 'pre_http_request', false, $r, $url );
		if ( false !== $pre )
			return $pre;

		if ( function_exists( 'wp_kses_bad_protocol' ) ) {
			if ( $r['reject_unsafe_urls'] )
				$url = wp_http_validate_url( $url );
			if ( $url ) {
				$url = wp_kses_bad_protocol( $url, array( 'http', 'https', 'ssl' ) );
			}
		}

		$arrURL = @parse_url( $url );

		if ( empty( $url ) || empty( $arrURL['scheme'] ) )
			return new WP_Error('http_request_failed', __('A valid URL was not provided.'));

		if ( $this->block_request( $url ) )
			return new WP_Error( 'http_request_failed', __( 'User has blocked requests through HTTP.' ) );

		/*
		 * Determine if this is a https call and pass that on to the transport functions
		 * so that we can blacklist the transports that do not support ssl verification
		 */
		$r['ssl'] = $arrURL['scheme'] == 'https' || $arrURL['scheme'] == 'ssl';

		// Determine if this request is to OUR install of WordPress.
		$homeURL = parse_url( get_bloginfo( 'url' ) );
		$r['local'] = 'localhost' == $arrURL['host'] || ( isset( $homeURL['host'] ) && $homeURL['host'] == $arrURL['host'] );
		unset( $homeURL );

		/*
		 * If we are streaming to a file but no filename was given drop it in the WP temp dir
		 * and pick its name using the basename of the $url.
		 */
		if ( $r['stream']  && empty( $r['filename'] ) ) {
			$r['filename'] = get_temp_dir() . wp_unique_filename( get_temp_dir(), basename( $url ) );
		}

		/*
		 * Force some settings if we are streaming to a file and check for existence and perms
		 * of destination directory.
		 */
		if ( $r['stream'] ) {
			$r['blocking'] = true;
			if ( ! wp_is_writable( dirname( $r['filename'] ) ) )
				return new WP_Error( 'http_request_failed', __( 'Destination directory for file streaming does not exist or is not writable.' ) );
		}

		if ( is_null( $r['headers'] ) )
			$r['headers'] = array();

		if ( ! is_array( $r['headers'] ) ) {
			$processedHeaders = self::processHeaders( $r['headers'], $url );
			$r['headers'] = $processedHeaders['headers'];
		}

		if ( isset( $r['headers']['User-Agent'] ) ) {
			$r['user-agent'] = $r['headers']['User-Agent'];
			unset( $r['headers']['User-Agent'] );
		}

		if ( isset( $r['headers']['user-agent'] ) ) {
			$r['user-agent'] = $r['headers']['user-agent'];
			unset( $r['headers']['user-agent'] );
		}

		if ( '1.1' == $r['httpversion'] && !isset( $r['headers']['connection'] ) ) {
			$r['headers']['connection'] = 'close';
		}

		// Construct Cookie: header if any cookies are set.
		self::buildCookieHeader( $r );

		// Avoid issues where mbstring.func_overload is enabled.
		mbstring_binary_safe_encoding();

		if ( ! isset( $r['headers']['Accept-Encoding'] ) ) {
			if ( $encoding = WP_Http_Encoding::accept_encoding( $url, $r ) )
				$r['headers']['Accept-Encoding'] = $encoding;
		}

		if ( ( ! is_null( $r['body'] ) && '' != $r['body'] ) || 'POST' == $r['method'] || 'PUT' == $r['method'] ) {
			if ( is_array( $r['body'] ) || is_object( $r['body'] ) ) {
				$r['body'] = http_build_query( $r['body'], null, '&' );

				if ( ! isset( $r['headers']['Content-Type'] ) )
					$r['headers']['Content-Type'] = 'application/x-www-form-urlencoded; charset=' . get_option( 'blog_charset' );
			}

			if ( '' === $r['body'] )
				$r['body'] = null;

			if ( ! isset( $r['headers']['Content-Length'] ) && ! isset( $r['headers']['content-length'] ) )
				$r['headers']['Content-Length'] = strlen( $r['body'] );
		}

		$response = $this->_dispatch_request( $url, $r );

		reset_mbstring_encoding();

		if ( is_wp_error( $response ) )
			return $response;

		// Append cookies that were used in this request to the response
		if ( ! empty( $r['cookies'] ) ) {
			$cookies_set = wp_list_pluck( $response['cookies'], 'name' );
			foreach ( $r['cookies'] as $cookie ) {
				if ( ! in_array( $cookie->name, $cookies_set ) && $cookie->test( $url ) ) {
					$response['cookies'][] = $cookie;
				}
			}
		}

		return $response;
	}

	public function _get_first_available_transport( $args, $url = null ) {
		/**
		 * Filter which HTTP transports are available and in what order.
		 *
		 * @since 3.7.0
		 *
		 * @param array  $value Array of HTTP transports to check. Default array contains
		 *                      'curl', and 'streams', in that order.
		 * @param array  $args  HTTP request arguments.
		 * @param string $url   The URL to request.
		 */
		$request_order = apply_filters( 'http_api_transports', array( 'curl', 'streams' ), $args, $url );

		// Loop over each transport on each HTTP request looking for one which will serve this request's needs.
		foreach ( $request_order as $transport ) {
			$class = 'WP_HTTP_' . $transport;

			// Check to see if this transport is a possibility, calls the transport statically.
			if ( !call_user_func( array( $class, 'test' ), $args, $url ) )
				continue;

			return $class;
		}

		return false;
	}

	private function _dispatch_request( $url, $args ) {
		static $transports = array();

		$class = $this->_get_first_available_transport( $args, $url );
		if ( !$class )
			return new WP_Error( 'http_failure', __( 'There are no HTTP transports available which can complete the requested request.' ) );

		// Transport claims to support request, instantiate it and give it a whirl.
		if ( empty( $transports[$class] ) )
			$transports[$class] = new $class;

		$response = $transports[$class]->request( $url, $args );

		/**
		 * Fires after an HTTP API response is received and before the response is returned.
		 *
		 * @since 2.8.0
		 *
		 * @param array|WP_Error $response HTTP response or WP_Error object.
		 * @param string         $context  Context under which the hook is fired.
		 * @param string         $class    HTTP transport used.
		 * @param array          $args     HTTP request arguments.
		 * @param string         $url      The request URL.
		 */
		do_action( 'http_api_debug', $response, 'response', $class, $args, $url );

		if ( is_wp_error( $response ) )
			return $response;

		/**
		 * Filter the HTTP API response immediately before the response is returned.
		 *
		 * @since 2.9.0
		 *
		 * @param array  $response HTTP response.
		 * @param array  $args     HTTP request arguments.
		 * @param string $url      The request URL.
		 */
		return apply_filters( 'http_response', $response, $args, $url );
	}

	public function post($url, $args = array()) {
		$defaults = array('method' => 'POST');
		$r = wp_parse_args( $args, $defaults );
		return $this->request($url, $r);
	}

	public function get($url, $args = array()) {
		$defaults = array('method' => 'GET');
		$r = wp_parse_args( $args, $defaults );
		return $this->request($url, $r);
	}

	public function head($url, $args = array()) {
		$defaults = array('method' => 'HEAD');
		$r = wp_parse_args( $args, $defaults );
		return $this->request($url, $r);
	}

	public static function processResponse($strResponse) {
		$res = explode("\r\n\r\n", $strResponse, 2);

		return array('headers' => $res[0], 'body' => isset($res[1]) ? $res[1] : '');
	}

	public static function processHeaders( $headers, $url = '' ) {
		// Split headers, one per array element.
		if ( is_string($headers) ) {
			// Tolerate line terminator: CRLF = LF (RFC 2616 19.3).
			$headers = str_replace("\r\n", "\n", $headers);
			/*
			 * Unfold folded header fields. LWS = [CRLF] 1*( SP | HT ) <US-ASCII SP, space (32)>,
			 * <US-ASCII HT, horizontal-tab (9)> (RFC 2616 2.2).
			 */
			$headers = preg_replace('/\n[ \t]/', ' ', $headers);
			// Create the headers array.
			$headers = explode("\n", $headers);
		}

		$response = array('code' => 0, 'message' => '');

		/*
		 * If a redirection has taken place, The headers for each page request may have been passed.
		 * In this case, determine the final HTTP header and parse from there.
		 */
		for ( $i = count($headers)-1; $i >= 0; $i-- ) {
			if ( !empty($headers[$i]) && false === strpos($headers[$i], ':') ) {
				$headers = array_splice($headers, $i);
				break;
			}
		}

		$cookies = array();
		$newheaders = array();
		foreach ( (array) $headers as $tempheader ) {
			if ( empty($tempheader) )
				continue;

			if ( false === strpos($tempheader, ':') ) {
				$stack = explode(' ', $tempheader, 3);
				$stack[] = '';
				list( , $response['code'], $response['message']) = $stack;
				continue;
			}

			list($key, $value) = explode(':', $tempheader, 2);

			$key = strtolower( $key );
			$value = trim( $value );

			if ( isset( $newheaders[ $key ] ) ) {
				if ( ! is_array( $newheaders[ $key ] ) )
					$newheaders[$key] = array( $newheaders[ $key ] );
				$newheaders[ $key ][] = $value;
			} else {
				$newheaders[ $key ] = $value;
			}
			if ( 'set-cookie' == $key )
				$cookies[] = new WP_Http_Cookie( $value, $url );
		}

		// Cast the Response Code to an int
		$response['code'] = intval( $response['code'] );

		return array('response' => $response, 'headers' => $newheaders, 'cookies' => $cookies);
	}
	
	public static function buildCookieHeader( &$r ) {
		if ( ! empty($r['cookies']) ) {
			// Upgrade any name => value cookie pairs to WP_HTTP_Cookie instances.
			foreach ( $r['cookies'] as $name => $value ) {
				if ( ! is_object( $value ) )
					$r['cookies'][ $name ] = new WP_HTTP_Cookie( array( 'name' => $name, 'value' => $value ) );
			}

			$cookies_header = '';
			foreach ( (array) $r['cookies'] as $cookie ) {
				$cookies_header .= $cookie->getHeaderValue() . '; ';
			}

			$cookies_header = substr( $cookies_header, 0, -2 );
			$r['headers']['cookie'] = $cookies_header;
		}
	}

	public static function chunkTransferDecode( $body ) {
		// The body is not chunked encoded or is malformed.
		if ( ! preg_match( '/^([0-9a-f]+)[^\r\n]*\r\n/i', trim( $body ) ) )
			return $body;

		$parsed_body = '';

		// We'll be altering $body, so need a backup in case of error.
		$body_original = $body;

		while ( true ) {
			$has_chunk = (bool) preg_match( '/^([0-9a-f]+)[^\r\n]*\r\n/i', $body, $match );
			if ( ! $has_chunk || empty( $match[1] ) )
				return $body_original;

			$length = hexdec( $match[1] );
			$chunk_length = strlen( $match[0] );

			// Parse out the chunk of data.
			$parsed_body .= substr( $body, $chunk_length, $length );

			// Remove the chunk from the raw data.
			$body = substr( $body, $length + $chunk_length );

			// End of the document.
			if ( '0' === trim( $body ) )
				return $parsed_body;
		}
	}

	public function block_request($uri) {
		// We don't need to block requests, because nothing is blocked.
		if ( ! defined( 'WP_HTTP_BLOCK_EXTERNAL' ) || ! WP_HTTP_BLOCK_EXTERNAL )
			return false;

		$check = parse_url($uri);
		if ( ! $check )
			return true;

		$home = parse_url( get_option('siteurl') );

		// Don't block requests back to ourselves by default.
		if ( 'localhost' == $check['host'] || ( isset( $home['host'] ) && $home['host'] == $check['host'] ) ) {
			/**
			 * Filter whether to block local requests through the proxy.
			 *
			 * @since 2.8.0
			 *
			 * @param bool $block Whether to block local requests through proxy.
			 *                    Default false.
			 */
			return apply_filters( 'block_local_requests', false );
		}

		if ( !defined('WP_ACCESSIBLE_HOSTS') )
			return true;

		static $accessible_hosts = null;
		static $wildcard_regex = array();
		if ( null === $accessible_hosts ) {
			$accessible_hosts = preg_split('|,\s*|', WP_ACCESSIBLE_HOSTS);

			if ( false !== strpos(WP_ACCESSIBLE_HOSTS, '*') ) {
				$wildcard_regex = array();
				foreach ( $accessible_hosts as $host )
					$wildcard_regex[] = str_replace( '\*', '.+', preg_quote( $host, '/' ) );
				$wildcard_regex = '/^(' . implode('|', $wildcard_regex) . ')$/i';
			}
		}

		if ( !empty($wildcard_regex) )
			return !preg_match($wildcard_regex, $check['host']);
		else
			return !in_array( $check['host'], $accessible_hosts ); //Inverse logic, If it's in the array, then we can't access it.

	}

	protected static function parse_url( $url ) {
		$parts = @parse_url( $url );
		if ( ! $parts ) {
			// < PHP 5.4.7 compat, trouble with relative paths including a scheme break in the path
			if ( '/' == $url[0] && false !== strpos( $url, '://' ) ) {
				// Since we know it's a relative path, prefix with a scheme/host placeholder and try again
				if ( ! $parts = @parse_url( 'placeholder://placeholder' . $url ) ) {
					return $parts;
				}
				// Remove the placeholder values
				unset( $parts['scheme'], $parts['host'] );
			} else {
				return $parts;
			}
		}

		// < PHP 5.4.7 compat, doesn't detect schemeless URL's host field
		if ( '//' == substr( $url, 0, 2 ) && ! isset( $parts['host'] ) ) {
			list( $parts['host'], $slashless_path ) = explode( '/', substr( $parts['path'], 2 ), 2 );
			$parts['path'] = "/{$slashless_path}";
		}

		return $parts;
	}

	public static function make_absolute_url( $maybe_relative_path, $url ) {
		if ( empty( $url ) )
			return $maybe_relative_path;

		if ( ! $url_parts = WP_HTTP::parse_url( $url ) ) {
			return $maybe_relative_path;
		}

		if ( ! $relative_url_parts = WP_HTTP::parse_url( $maybe_relative_path ) ) {
			return $maybe_relative_path;
		}

		// Check for a scheme on the 'relative' url
		if ( ! empty( $relative_url_parts['scheme'] ) ) {
			return $maybe_relative_path;
		}

		$absolute_path = $url_parts['scheme'] . '://';

		// Schemeless URL's will make it this far, so we check for a host in the relative url and convert it to a protocol-url
		if ( isset( $relative_url_parts['host'] ) ) {
			$absolute_path .= $relative_url_parts['host'];
			if ( isset( $relative_url_parts['port'] ) )
				$absolute_path .= ':' . $relative_url_parts['port'];
		} else {
			$absolute_path .= $url_parts['host'];
			if ( isset( $url_parts['port'] ) )
				$absolute_path .= ':' . $url_parts['port'];
		}

		// Start off with the Absolute URL path.
		$path = ! empty( $url_parts['path'] ) ? $url_parts['path'] : '/';

		// If it's a root-relative path, then great.
		if ( ! empty( $relative_url_parts['path'] ) && '/' == $relative_url_parts['path'][0] ) {
			$path = $relative_url_parts['path'];

		// Else it's a relative path.
		} elseif ( ! empty( $relative_url_parts['path'] ) ) {
			// Strip off any file components from the absolute path.
			$path = substr( $path, 0, strrpos( $path, '/' ) + 1 );

			// Build the new path.
			$path .= $relative_url_parts['path'];

			// Strip all /path/../ out of the path.
			while ( strpos( $path, '../' ) > 1 ) {
				$path = preg_replace( '![^/]+/\.\./!', '', $path );
			}

			// Strip any final leading ../ from the path.
			$path = preg_replace( '!^/(\.\./)+!', '', $path );
		}

		// Add the Query string.
		if ( ! empty( $relative_url_parts['query'] ) )
			$path .= '?' . $relative_url_parts['query'];

		return $absolute_path . '/' . ltrim( $path, '/' );
	}

	public static function handle_redirects( $url, $args, $response ) {
		// If no redirects are present, or, redirects were not requested, perform no action.
		if ( ! isset( $response['headers']['location'] ) || 0 === $args['_redirection'] )
			return false;

		// Only perform redirections on redirection http codes.
		if ( $response['response']['code'] > 399 || $response['response']['code'] < 300 )
			return false;

		// Don't redirect if we've run out of redirects.
		if ( $args['redirection']-- <= 0 )
			return new WP_Error( 'http_request_failed', __('Too many redirects.') );

		$redirect_location = $response['headers']['location'];

		// If there were multiple Location headers, use the last header specified.
		if ( is_array( $redirect_location ) )
			$redirect_location = array_pop( $redirect_location );

		$redirect_location = WP_HTTP::make_absolute_url( $redirect_location, $url );

		// POST requests should not POST to a redirected location.
		if ( 'POST' == $args['method'] ) {
			if ( in_array( $response['response']['code'], array( 302, 303 ) ) )
				$args['method'] = 'GET';
		}

		// Include valid cookies in the redirect process.
		if ( ! empty( $response['cookies'] ) ) {
			foreach ( $response['cookies'] as $cookie ) {
				if ( $cookie->test( $redirect_location ) )
					$args['cookies'][] = $cookie;
			}
		}

		return wp_remote_request( $redirect_location, $args );
	}
	
	public static function is_ip_address( $maybe_ip ) {
		if ( preg_match( '/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', $maybe_ip ) )
			return 4;

		if ( false !== strpos( $maybe_ip, ':' ) && preg_match( '/^(((?=.*(::))(?!.*\3.+\3))\3?|([\dA-F]{1,4}(\3|:\b|$)|\2))(?4){5}((?4){2}|(((2[0-4]|1\d|[1-9])?\d|25[0-5])\.?\b){4})$/i', trim( $maybe_ip, ' []' ) ) )
			return 6;

		return false;
	}

}
