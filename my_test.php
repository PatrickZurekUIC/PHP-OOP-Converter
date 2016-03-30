<?php

require 'vendor/autoload.php';
use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\Node\Expr;
use PhpParser\PrettyPrinter;

$file = fopen($argv[1], "r");
$code = fread($file, filesize($argv[1]) );
fclose($file);

$parser = new PhpParser\Parser(new PhpParser\Lexer);
$serializer = new PhpParser\Serializer\XML;
$prettyPrinter = new PrettyPrinter\Standard;

try {
	$stmts = $parser->parse($code);
	var_dump($stmts);
	// echo $serializer->serialize($stmts);
	// echo $prettyPrinter->prettyPrintFile($stmts);

} catch (PhpParser\Error $e) {
	echo 'Parse Error: ', $e->getMessage();
}	