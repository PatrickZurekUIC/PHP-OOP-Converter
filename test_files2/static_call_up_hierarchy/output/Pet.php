<?php

function Pet__construct($args)
{
    $objInst = $args[0];
    echo 'code for construct in Pet
';
}
function Pet_petMethod(&$objInst)
{
    echo 'This is Pet\'s method petMethod
';
}
function Pet_petMethod2(&$objInst)
{
}
function Pet_privPetMethod(&$objInst)
{
}
function Pet_statMethod(&$objInst)
{
    echo 'Call to static statMethod in Pet
';
}
$Pet = array('__vars' => array());