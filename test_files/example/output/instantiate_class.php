<?php

$instance = array_merge($GLOBALS['TestClass']['__vars'], array('__type' => 'TestClass'));
TestClass__construct($instance);
$test_arr = array();
$test_arr[0] = array_merge($GLOBALS['TestClass']['__vars'], array('__type' => 'TestClass'));
TestClass__construct($test_arr[0]);