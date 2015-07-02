<?php
  include('Pet.php');
  class Dog extends Pet {
    public function bark() {
      echo $this->name . " says Woof\n";
    }
  }
