<?php


function test_args($args){
	echo "Echoing args\n";
	var_dump($args);
	echo $args[0];
	echo $args[1];
	echo $args[2];

}


test_args(1,2,3);


