<?php

class Greeting
{
    private $hello;
    public $helloFr;
    function __construct($hello, $helloFr)
    {
        $this->hello = $hello;
        $this->helloFr = $helloFr;
    }
    function sayHello($name)
    {
        return $this->hello . $name . "\n";
    }
    function sayHelloFr($name)
    {
        return $this->helloFr . $name . "\n";
    }
}
$greeting1 = new Greeting("Hello ", "Bonjour ");
$greeting2 = new Greeting("Hey There ", "Coucou ");

$a = array($greeting1, $greeting2);

$a[0] = $greeting2;
$a[1] = $greeting1;

echo $a[0]->sayHello("Sai");
echo $a[1]->sayHelloFr("Sai");
// echo $greeting->sayHello("Sai");
// echo $greeting2->sayHelloFr("Sai");