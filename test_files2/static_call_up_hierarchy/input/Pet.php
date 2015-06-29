<?php 
    class Pet {

        public function __construct() {
            echo "code for construct in Pet\n";
        }

        public function petMethod() {
            echo "This is Pet's method petMethod\n";
        }

        public function petMethod2() {

        }

        private function privPetMethod() {

        }

        static function statMethod() {
            echo "Call to static statMethod in Pet\n";
        }

    }

   
