<?php

include 'Pet.php';
include 'Dog.php';
include 'TestClass.php';
$test = array_merge($GLOBALS['TestClass']['__vars'], array('__type' => 'TestClass'));
TestClass__construct($test);
TestClass_set_fmap($test);