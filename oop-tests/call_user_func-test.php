<?php
function sayHi($greeting, $name){
	echo $greeting . $name;
}

$func = "sayHi";
// $args = array("Hi", "Sai");
// call_user_func($func, $args); This causes an error and no arguments are sent to the function sayHi()
call_user_func($func, "Hi ", "Sai"); 	// This successfully prints Hi Sai which means 
										// arguments have to be passed individually and not as an array


?>