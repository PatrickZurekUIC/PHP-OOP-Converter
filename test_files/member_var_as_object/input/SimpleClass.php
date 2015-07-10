<?php
    class SimpleClass {

        public $fmap;

        public function __construct() {

        }

        public function method1() {
            $fmap = new StubClass();
        }
    }


    class StubClass {
        public function method1() {
        
        }
    }


    $test = new SimpleClass();

    $test->method1();
