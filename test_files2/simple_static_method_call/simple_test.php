<?php

  class Pet {
  
    protected $name;

    public function __construct($name) {
      echo "Setting name to " . $name . "\n";
      $this->name = $name;
    } 
  }

  class Dog extends Pet { 

    public $age;

    public function bark() {
      echo "Woof. I am " . $this->age . " years old.\n";
    }

    public function setAge($age) {
      $this->age = $age;
    }

    public static function staticFunc() {
      echo "This is a static function.\n";
    }

  }
  

  $a_pet = new Dog("Spike"); 

  Dog::staticFunc();

?>
