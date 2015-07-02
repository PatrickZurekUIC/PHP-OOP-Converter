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
    //public static $my_static;
    public static $my_static = "foo";
    public $weight;
    public function setWeightAndEat($weight) {
      //$this->weight = $weight;  
      //parent::eat();
      parent::testStaticFunc("test");
      echo "Accessing static var using 'self.'  Var is: " . self::$my_static . "\n";
      //echo "Trying to access a static function: " . self::static_func();
      return "Test";
    }
    public static function static_func($test) {
      echo "This is a static function.\n";
    }
  }

  $var = 30;
  $a_terrier = new Terrier("Spike"); 
  $a_terrier->setWeightAndEat($var);
  echo "Got string: " . $a_terrier->testFunc() . "\n"; 
  echo "Static var is: " . Terrier::$my_static . "\n";
  Terrier::static_func("test");
?>
