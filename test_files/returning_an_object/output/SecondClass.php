<?php

function SecondClass__construct(&$objInst)
{
}
function SecondClass_return_object(&$objInst)
{
    $ = array_merge($GLOBALS['TopLevelClass']['__vars'], array('__type' => 'TopLevelClass'));
    TopLevelClass__construct($);
}
$SecondClass = array('__vars' => array());