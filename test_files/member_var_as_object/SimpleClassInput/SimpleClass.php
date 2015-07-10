<?php
    class SimpleClass {

        public $fmap = array();
        public $non_array;

        public function __construct() {

        }

        public function method1() {
            $local = array();

            $this->non_array = new StubClass();
            $this->non_array->method1();
            
            $this->fmap[0] = new StubClass();
            $this->fmap[0]->method1();

            $local[0] = new StubClass();
            $local[0]->method1();
        }
    }


    class StubClass {

        public function __construct() {
            echo "This is Stubclass construct \n";
        }

        public function method1() {
            echo "This is method 1 in StubClass\n";        
        }
    }


    $test = new SimpleClass();

    $test->method1();
