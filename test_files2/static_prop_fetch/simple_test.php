<?php

  $testGlobalVar = "hi";

  class TopLevelClass {

    public static $myStatic = 'topLevelStatic';
  }

  class Pet extends TopLevelClass {
  
    protected $name;
    public static $petStaticVar = 1;

    public function __construct($name) {
      echo "Setting name to " . $name . "\n";
      $this->name = $name;
    } 
    public static function ignoreMe() {
      return 1;
    }
  }

  class Dog extends Pet { 

    public $age;
    public static $testStaticVar = 10;

    public function bark() {
      echo "Woof. I am " . $this->age . " years old.\n";
    }

    public function setAge($age) {
      $this->age = $age;
      echo "Parent static var: " . self::$petStaticVar . "\n";
      // Above gets converted to Pet_petStaticVar
      echo "Parent static var: " . parent::$petStaticVar . "\n";
      // Above gets converted to Pet_petStaticVar
      echo "Top Level Class static: " . self::$myStatic . "\n";
      // Above gets converted to TopLevelClass_myStatic
      echo "Pet static var: " . Pet::$myStatic;
      // Above gets converted to TopLevelCLass_myStatic;
      echo "TopLevelClass_myStatic: " . parent::$myStatic . "\n";
      // Above gets converted to TopLevelClass_myStatic
      global $testGlobalVar;
      echo $testGlobalVar;
      
    }
  }
  

  $a_pet = new Dog("Spike"); 
  $a_pet->setAge(10);
  echo Dog::$testStaticVar . "\n"; 
  // Handle the case when the static var is not in the class on the LHS, but in its parent
  echo Dog::$petStaticVar . "\n";

  Dog::$petStaticVar++;
  echo Dog::$petStaticVar . "\n";

  echo Pet::$petStaticVar . "\n";

// Handle parent::$my_static
// Handle self::$my_static
?>
