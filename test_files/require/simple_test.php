<?php
  require 'additional_file.php';


  class Pet extends BaseObject {
  
    protected $name;

    public function __construct($name) {
      echo "Setting name to " . $name . "\n";
      $this->name = $name;
    } 

    public function eat() {
      echo $this->name . " is eating.\n";
    }
  }

  $a_pet = new Pet("Spike"); 
?>
