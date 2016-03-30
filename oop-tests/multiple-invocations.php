<?php 
class Greeting{ 
	public $hello; 
	public $helloFr;

	function __construct($hello = NULL , $helloFr = NULL){
		if($hello){
			echo "assigning helloFr\n";
			$this->hello = $hello;
		}
		if($helloFr){
			echo "assigning hello\n";
			$this->helloFr = $helloFr;
		}
	}

	function sayHello($name){
		if($this->hello){
			return $this->hello.$name; 
		}
		return "";
	}
	function sayHelloFr($name){
		if($this->helloFr){
			return $this->helloFr.$name;
		}
		return "";
	}
}

class Greets{

	public $englishGreetObj; 
	public $frenchGreetObj;

	function __construct($englishGreet, $frenchGreet){
		echo "constructor for Greets called\n";
		$this->englishGreetObj = new Greeting($englishGreet, NULL);
		$this->frenchGreetObj = new Greeting(NULL, $frenchGreet);
	}

}


$greet1 = new Greets("Hello ", "Bonjour ");
$greet2 = new Greets("Hey There ", "Coucou ");
echo $greet1->englishGreetObj->sayHello("Sai");
echo $greet2->frenchGreetObj->sayHelloFr("Sai");