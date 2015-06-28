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

    public function testFunc() {
      echo "Just a test function\n";
      return "Hello";
    }
 
    public static function testStaticFunc() {
      return "bar";
    }
  }

  class Dog extends Pet {
    public function bark() {
      echo $this->name . " says Woof\n";
    }
  }

  class Terrier extends Dog {
    public $weight;
    public function setWeight($weight) {
      $this->weight = $weight;
    }
  }

  $var = 30;
  $a_terrier = new Terrier("Spike"); 
  $a_terrier->setWeight($var);
?>
