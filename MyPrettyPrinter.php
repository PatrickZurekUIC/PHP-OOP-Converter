<?php
namespace PhpParser\PrettyPrinter;

require 'vendor/autoload.php';

use PhpParser\PrettyPrinter;
use PhpParser\Node\Scalar;


class MyPrettyPrinter extends PrettyPrinter\Standard
{
	public function pScalar_String(Scalar\String_ $node) {
		if ($node->hasAttribute('originalValue')) {
			return $node->getAttribute('originalValue');
		}
		return parent::pScalar_String($node);
	}
}
