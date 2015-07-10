<?php
    
    class TestClass {
 
        public $fmap = array();
        public $test_this;

        public function __construct() {

        }

        public $property = array();

        public function set_fmap() {
            foreach (array("paperSummary", "commentsToAuthor", "commentsToPC",
                       "commentsToAddress", "weaknessOfPaper",
                       "strengthOfPaper", "textField7", "textField8") as $fid) {
                //$this->fmap[$fid] = new ReviewField($fid);
                $normal_var = new ReviewField($fid);
                //$this->test_this = new ReviewField($fid);
                $normal_var->getField();
            }
        }

        public function get_fmap() {
            return $this->fmap;
        }
    }

    class ReviewField {
        private $fid;
        function __construct($fid) {
            $this->fid = $fid;
        }

        function getField() {
            return $this->fid;
        }

    }

