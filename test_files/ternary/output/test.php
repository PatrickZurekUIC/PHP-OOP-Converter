<?php

include 'test_class.php';
$t = array_merge($GLOBALS['Items']['__vars'], array('__type' => 'Items'));
Items__construct($t);
Items_test_func($t);