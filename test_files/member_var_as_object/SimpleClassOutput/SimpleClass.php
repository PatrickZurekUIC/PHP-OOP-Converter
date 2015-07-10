<?php

function SimpleClass__construct($args)
{
    $objInst = $args[0];
}
function SimpleClass_method1(&$objInst)
{
    $local = array();
    $non_array = array_merge($GLOBALS['StubClass']['__vars'], array('__type' => 'StubClass'));
    StubClass__construct($non_array);
    StubClass_method1($non_array);
    $ = array_merge($GLOBALS['StubClass']['__vars'], array('__type' => 'StubClass'));
    StubClass__construct($);
    StubClass_method1($);
    $ = array_merge($GLOBALS['StubClass']['__vars'], array('__type' => 'StubClass'));
    StubClass__construct($);
    StubClass_method1($);
}
$SimpleClass = array('__vars' => array('fmap' => array(), 'non_array' => null));
function StubClass__construct($args)
{
    $objInst = $args[0];
    echo 'This is Stubclass construct 
';
}
function StubClass_method1(&$objInst)
{
    echo 'This is method 1 in StubClass
';
}
$StubClass = array('__vars' => array());
$test = array_merge($GLOBALS['SimpleClass']['__vars'], array('__type' => 'SimpleClass'));
SimpleClass__construct($test);
SimpleClass_method1($test);