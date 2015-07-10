<?php

    include("Pet.php");
    include("Dog.php");
    include("TestClass.php");

    $test = new TestClass();

    $test->set_fmap();

//    $fmap = $test->get_fmap();

//    foreach ($fmap as $map) {
//        echo "Found " . $map->getField() . "\n";
//    }

