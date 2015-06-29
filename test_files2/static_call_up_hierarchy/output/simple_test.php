<?php

include 'Pet.php';
include 'Dog.php';
include 'Poodle.php';
$poodle = array_merge($GLOBALS['Poodle']['__vars'], array('__type' => 'Poodle'));
Pet__construct($poodle);
Pet_petMethod($poodle);
Poodle_poodleMethod2($poodle);