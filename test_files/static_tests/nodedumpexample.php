<?php

    include ('Pet.php');
    include ('Dog.php');

    Dog::statMethod();

    function Pet_statMethod(){

    }


    $method = new ReflectionMethod('Dog', 'statMethod');
    $reflecting_class = $method->getDeclaringClass();
    $dec_class = $reflecting_class->getName();
    call_user_func($dec_class . '_statMethod', null);
