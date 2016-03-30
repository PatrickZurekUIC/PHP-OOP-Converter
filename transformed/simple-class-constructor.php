<?php

function sayHi($hi, $name)
{
    return $hi . " " . $name . "\n";
}
function Greeting__construct(&$objInst, $hello, $helloFr)
{
    $objInst['hello'] = $hello;
    $objInst['helloFr'] = $helloFr;
}
function Greeting_sayHello(&$objInst, $name)
{
    return $objInst['hello'] . $name . "\n";
}
function Greeting_sayHelloFr(&$objInst, $name)
{
    return $objInst['helloFr'] . $name . "\n";
}
$Greeting = array('__vars' => array('helloFr' => null));
$greeting = array_merge($GLOBALS['Greeting']['__vars'], array('__type' => 'Greeting'));
Greeting__construct($greeting, "Hello ", "Bonjour ");
$greeting2 = array_merge($GLOBALS['Greeting']['__vars'], array('__type' => 'Greeting'));
Greeting__construct($greeting2, "Hey There ", "Coucou ");
echo Greeting_sayHello($greeting, "Sai");
echo Greeting_sayHelloFr($greeting2, "Sai");
echo sayHi("Hi", "Sai");