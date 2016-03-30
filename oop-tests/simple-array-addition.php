<?php

$a = array();

$b = array(1,2,3);

$a[] = 0;
$c = 1;
$d = array_merge($b, $a, $c);

var_dump($d);

?>