<?php

  class Pet {
  
    protected $name;
    protected $age = 10;

    public function __construct($name, $age) {
      echo "Setting name to " . $name . "\n";
      $this->name = $name;
      echo "Now name is set to " . $this->name . "\n";
      $this->age = $age;
    } 

    public function eat() {
      echo $this->name . " is eating.\n";
    }
  }

  class Dog extends Pet {
    public function bark() {
      echo $this->name . " says Woof\n";
    }
  }

  class Terrier extends Dog {
    public $testing;
    public $weight  = null;
  }

  $a_terrier = new Terrier("Spike", 5); 
  $a_terrier->bark();
  $a_terrier->eat();

?>
