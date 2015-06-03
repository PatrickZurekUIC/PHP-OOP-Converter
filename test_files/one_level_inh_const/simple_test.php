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

  class Dog extends Pet { 

    public $age;
    public $weight;
    
    const COLLARED = true; 

    public function bark() {
      echo "Woof. I am " . $this->age . " years old.\n";
    }

    public function setAge($age) {
      $this->age = $age;
    }
  }
  

  $a_pet = new Dog("Spike"); 

  echo "Is collared: " . Dog::COLLARED . "\n";
  
?>
