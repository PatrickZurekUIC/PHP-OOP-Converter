<?php

    class Poodle extends Dog {

        public function poodleMethod() {

        }

        public function poodleMethod2() {
            echo "This is a call to PoodleMethod2\n";
            parent::statMethod();
        }

        static function privPoodleMethod() {
            echo "This is a call to privPoodleMethod in Poodle.\n";
        }
    }

