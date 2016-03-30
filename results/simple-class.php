<?php

function Greeting_sayHello(&$objInst, $name)
{
    return $objInst['hello'] . $name;
}
function Greeting_sayHelloFr(&$objInst, $name)
{
    return $objInst['helloFr'] . $name;
}
$Greeting = array('__vars' => array('hello' => 'Hello ', 'helloFr' => null));
$greeting = array_merge($GLOBALS['Greeting']['__vars'], array('__type' => 'Greeting'));
echo Greeting_sayHello($greeting, 'Sai');
?>
 