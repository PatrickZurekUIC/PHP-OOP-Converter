<?php

include 'SimpleClass.php';
include 'OtherClass.php';
echo 'this is a test
';
$sc = array_merge($GLOBALS['SimpleClass']['__vars'], array('__type' => 'SimpleClass'));
SimpleClass__construct($sc);
SimpleClass_printOtherClassVal($sc);