<?php

function Pet__construct($args)
{
    $objInst = $args[0];
    echo 'code for construct';
}
function Pet_petMethod(&$objInst)
{
    echo 'This is Pet\'s method';
}
function Pet_petMethod2(&$objInst)
{
}
function Pet_privPetMethod(&$objInst)
{
}
$Pet = array('__vars' => array());