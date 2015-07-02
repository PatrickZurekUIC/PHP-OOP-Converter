<?php
    include 'Pet.php';
    include 'Dog.php';

    $a_dog = new Dog();


    $reflectionClass = new ReflectionClass('Dog');
    $val = $reflectionClass->getStaticPropertyValue("statVal");

    echo "Stat val is " . Dog::$statVal;
    echo "Stat val is " . $val;


    $methods = $reflectionClass->getMethods();
    
    array_walk($methods, function (&$v) {
        $v = $v->getName();
    });

    if (in_array("statMethod", $methods)) {
        echo "True";
    } else {
        echo "False";
    }
