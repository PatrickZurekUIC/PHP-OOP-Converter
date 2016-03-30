<?php

$a = array(1,3,5,6);

$b = array(7,9);

array_splice($a, 3, count($b), $b);
var_dump($a);

?>