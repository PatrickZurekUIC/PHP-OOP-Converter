<?php

function Dog__construct($args)
{
    $objInst = $args[0];
}
function Dog_dogMethod(&$objInst)
{
}
function Dog_dogMethod2(&$objInst)
{
}
function Dog_privDogMethod(&$objInst)
{
}
$Dog = array('__parent' => 'Pet', '__vars' => array_merge($Pet['__vars'], array()));