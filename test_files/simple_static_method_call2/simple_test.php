<?php

  class Pet {
  
    protected $name;

    public function __construct($name) {
      echo "Setting name to " . $name . "\n";
      $this->name = $name;
    } 

    public static function staticFunc() {
      echo "This is a static function in Pet.\n";
    }
  }

  class Dog extends Pet { 

    public $age;

    public function bark() {
      echo "Woof. I am " . $this->age . " years old.\n";
    }

    public function setAge($age) {
      $this->age = $age;
      self::staticFunc();
    }
   
    public static function staticFunc() {
      echo "This is a static function in Dog.\n";
    }

  }
  

  $a_pet = new Dog("Spike"); 
  $a_pet->setAge(10);

  Dog::staticFunc();

?>
