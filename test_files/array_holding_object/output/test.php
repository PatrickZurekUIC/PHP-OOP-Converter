<?php

include 'SimpleClass.php';
$obj[0][5][1] = array_merge($GLOBALS['SimpleClass']['__vars'], array('__type' => 'SimpleClass'));
SimpleClass__construct($obj[0][5][1]);
echo 'Val of obj\'s var is ' . $obj[0][5][1]['var1'] . '
';