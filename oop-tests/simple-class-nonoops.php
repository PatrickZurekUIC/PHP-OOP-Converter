<?php

function Greeting___construct($hello, $helloFr)
{	
	global $Greeting;
    $Greeting['hello'] = $hello;
    $Greeting['helloFr'] = $helloFr;
}
function Greeting_sayHello($name)
{	
	global $Greeting;
    return $Greeting['hello'] . $name;
}
function Greeting_sayHelloFr($name)
{
	global $Greeting;
    return $Greeting['helloFr'] . $name;
}
$Greeting = array('Greeting_hello' => NULL, 'Greeting_helloFr' => NULL, '__construct' => 'Greeting___construct', 'sayHello' => 'Greeting_sayHello', 'sayHelloFr' => 'Greeting_sayHelloFr');
call_user_func($Greeting['__construct'], 'Hello ', 'Bonjour ');
$greeting = $Greeting;
echo call_user_func($greeting['sayHello'], 'Sai');
echo call_user_func($greeting['sayHelloFr'], 'Sai');
?>
