<?php

function Poodle_poodleMethod(&$objInst)
{
}
function Poodle_poodleMethod2(&$objInst)
{
    echo 'This is a call to PoodleMethod2
';
    Pet_statMethod();
}
function Poodle_privPoodleMethod()
{
    echo 'This is a call to privPoodleMethod in Poodle.
';
}
$Poodle = array('__parent' => 'Dog', '__vars' => array_merge($Dog['__vars'], array()));