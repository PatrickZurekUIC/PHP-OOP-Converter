<?php

include 'Pet.php';
include 'Dog.php';
$reflection_method = new ReflectionMethod('Dog', 'statMethod');
$declaring_class = $reflection_method->getDeclaringClass();
$dec_class_name = $declaring_class->getName();
call_user_func($dec_class_name . '_statMethod', null);