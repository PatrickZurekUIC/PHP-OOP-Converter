<?php

function SimpleClass__construct(&$objInst)
{
    $var1 = array_merge($GLOBALS['OtherClass']['__vars'], array('__type' => 'OtherClass'));
    OtherClass__construct($var1);
}
function SimpleClass_printOtherClassVal(&$objInst)
{
    echo 'Value is: ' . $var1['var2'] . '
';
}
$SimpleClass = array('__vars' => array('var1' => null, 'var2' => null));