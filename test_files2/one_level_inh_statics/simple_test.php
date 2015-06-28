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
    public $weight = 10;
    
    static $should_be_collared = true;
    static $test_static2;

    public function bark() {
      echo "Woof. I am " . $this->age . " years old.\n";
    }

    public function setAge($age) {
      $this->age = $age;
    }
  }
  

  $a_pet = new Dog("Spike"); 

  echo "Should be collared: " . Dog::$should_be_collared . "\n";

  Dog::$test_static2 = 5;
  
?>
