<?php

  class Pet {
    private $name;
    function __construct($name) {
      $this->name = $name;
    }
   
    function getName() {
      return $this->name;
    }

  }

  class Dog extends Pet {
    
  }

  class Terrier extends Dog {

  }

  $a_pet = new Pet("Spike");

  echo "Pet name: " . $a_pet->getName();


  $a_dog = new Dog("hey");

  echo "Dog's name: " . $a_dog->getName();


  function Dog__construct($objInst) {
  //  $parentClass = new ReflectionClass::getParentClass();
   // $parentConstructor = $parentClass->getConstructor();

  }

  function Terrier__construct() {
    
  }

  function CarClass__construct() {
    VehicleClass__construct(func_get_args());
  }

  function VehicleClass__construct() {
    BaseObjectClass__construct(func_get_args());
  }

  function BaseObjectClass__construct($args) {
    $objInst = $args[0];
    $name = $args[1];
    echo "Name is " . $name;
  }

  VehicleClass__construct(null, "Spike");


