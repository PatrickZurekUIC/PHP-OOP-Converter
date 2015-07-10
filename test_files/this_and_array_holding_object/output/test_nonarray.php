<?php

include 'SimpleClass.php';
$obj = array_merge($GLOBALS['SimpleClass']['__vars'], array('__type' => 'SimpleClass'));
SimpleClass__construct($obj);
echo 'Val of obj\'s var is ' . $obj['var1'] . '
';