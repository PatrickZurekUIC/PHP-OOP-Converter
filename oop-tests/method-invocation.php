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
		return $this->get_hello() .$name . "\n"; 
	}
	function sayHelloFr($name){
		return $this->get_helloFr() .$name . "\n";
	}

	function get_hello(){
		return $this->hello;
	}

	function set_hello($hello){
		$this->hello = $hello;
	}

	function get_helloFr(){
		return $this->hello;
	}

	function set_helloFr($helloFr){
		$this->hello = $helloFr;
	}
} 

$greeting = new Greeting("Hello ", "Bonjour ");
$greeting2 = new Greeting("Hey There ", "Coucou ");
echo $greeting->sayHello("Sai");
echo $greeting2->sayHelloFr("Sai");
echo sayHi("Hi", "Sai");