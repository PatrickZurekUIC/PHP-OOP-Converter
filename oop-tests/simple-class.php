<?php 
class Greeting{ 
	private $hello = 'Hello '; 
	public $helloFr;

	function sayHello($name){ 
		return $this->hello.$name; 
	}
	function sayHelloFr($name){
		return $this->helloFr.$name;
	}
} 

$greeting = new Greeting;
echo $greeting->sayHello("Sai");

?> 