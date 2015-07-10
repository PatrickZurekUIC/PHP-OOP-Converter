<?php

include 'TopLevelClass.php';
include 'SecondClass.php';
$sc = array_merge($GLOBALS['SecondClass']['__vars'], array('__type' => 'SecondClass'));
SecondClass__construct($sc);
$obj = SecondClass_return_object($sc);