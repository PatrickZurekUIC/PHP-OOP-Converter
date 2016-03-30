<?php
namespace PhpParser\Lexer;

require 'vendor/autoload.php';

use PhpParser\Lexer;
use PhpParser\Parser\Tokens;

class KeepOriginalValueLexer extends Lexer\Emulative // or Lexer
{
    public function getNextToken(&$value = null, &$startAttributes = null, &$endAttributes = null) {
        $tokenId = parent::getNextToken($value, $startAttributes, $endAttributes);

        if ($tokenId == Tokens::T_CONSTANT_ENCAPSED_STRING)
        {
            // could also use $startAttributes, doesn't really matter here
            $endAttributes['originalValue'] = $value;
        }

        return $tokenId;
    }
}


            // || $tokenId == Tokens::T_LNUMBER               // integer
            // || $tokenId == Tokens::T_DNUMBER               // floating point number