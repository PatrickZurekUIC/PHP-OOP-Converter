<?php

include 'TopLevelClass.php';
$tlc = array_merge($GLOBALS['TopLevelClass']['__vars'], array('__type' => 'TopLevelClass'));
TopLevelClass__construct($tlc);
echo 'First in array is: ' . $TopLevelClass_myStatic[0];