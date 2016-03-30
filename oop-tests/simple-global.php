<?php

$global_test = array("one"=> "Sai", "two"=> "Prasanth", "three" => "Kommini");

function sayHello($index){
	global $global_test;
	return "Hello " . $global_test[$index];
}

echo sayHello("one")

?>