<?php 
class Greeting{ 
	function sayHello($greeting){ 
		return $greeting; 
	}
	function sayHelloFr($greeting){
		return $greeting;
	}
} 

$greeting = new Greeting;
echo $greeting->sayHello("Hello");
echo $greeting->sayHelloFr("Bonjour")

?> 