<?php
  include('Dog.php');
  class Terrier extends Dog {
    public $weight;
    public function setWeightAndEat($weight) {
      $this->weight = $weight;
    }
  }

