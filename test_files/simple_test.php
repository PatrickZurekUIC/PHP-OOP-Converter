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

  $var = 30;
  $a_pet = new Pet("Spike"); 
  $a_pet->eat();
?>
