<?php

    include("Pet.php");


    class Dog extends Pet {
        private $test_var;

        function doesnothing() {
            echo "hi";
        }
    }

    class Terrier extends Dog {
        private $temp_var;
        
        function doesnothing2() {
            echo "hello";
        }
    }

    echo "Does nothing";
      
    $a_terrier = new Terrier("Spike");
