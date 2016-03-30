<?php 
function sayHi($hi, $name){
    return $hi. " ".$name."\n";
}

class Greeting{ 
	private $hello; 
	public $helloFr;

	function __construct($hello, $helloFr){
		$this->hello = $hello;
		$this->helloFr = $helloFr;
	}

	function sayHello($name){ 
		return $this->hello.$name . "\n"; 
	}
	function sayHelloFr($name){
		return $this->helloFr.$name . "\n";
	}
} 

$greeting = new Greeting("Hello ", "Bonjour ");
$greeting2 = new Greeting("Hey There ", "Coucou ");
echo $greeting->sayHello("Sai");
echo $greeting2->sayHelloFr("Sai");
echo sayHi("Hi", "Sai");