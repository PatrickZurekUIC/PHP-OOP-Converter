<?php

    class SimpleClass {

        public $var1;
        public $var2;

        function __construct() {
            $this->var1 = new OtherClass();
        }

        function printOtherClassVal() {
            echo "Value is: " . $this->var1->var2 . "\n";
        }
    }
