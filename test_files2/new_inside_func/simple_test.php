<?php

  class Pet {
  
    protected $name;

    public function __construct($name) {
      echo "Setting name to " . $name . "\n";
      $this->name = $name;
    } 

    public function eat() {
      echo $this->name . " is eating.\n";
    }
  }



  function test_func() {
    $a_pet = new Pet("Spike"); 
    echo "Hi";
  }


  test_func();
?>
