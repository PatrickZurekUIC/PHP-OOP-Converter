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

  $a_pet = new Pet("Spike"); 

?>
<?php

  class TestClass {
    public function __construct() {
      $test_pet = new Pet("Bowser");
    }

  }

?>
<?php

  $test = new TestClass();

?>
