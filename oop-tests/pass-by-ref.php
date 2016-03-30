<?php 
function receiveRef(&$arr){
	$arr['one']++;
}

$arr = array("one"=> 1, "two" => 2, "three" => 3);

receiveRef($arr);
echo $arr["one"];


