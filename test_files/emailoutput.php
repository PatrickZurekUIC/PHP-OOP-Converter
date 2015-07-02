<?php

function Pet__construct(&$objInst, $name)
{
    echo 'Setting name to ' . $name . '
';
    $objInst['name'] = $name;
}
function Pet_eat(&$objInst)
{
    echo $objInst['name'] . ' is eating.
';
}
function Pet_testFunc(&$objInst)
{
    echo 'Just a test function
';
    return 'Hello';
}
function Pet_testStaticFunc()
{
    return 'bar';
}
$Pet = array('__vars' => array('name' => null));
function Dog_bark(&$objInst)
{
    echo $objInst['name'] . ' says Woof
';
}
$Dog = array('__parent' => 'Pet', '__vars' => array_merge($Pet['__vars'], array()));
function Terrier_setWeight(&$objInst, $weight)
{
    $objInst['weight'] = $weight;
}
$Terrier = array('__parent' => 'Dog', '__vars' => array_merge($Dog['__vars'], array('weight' => null)));
$var = 30;
$a_terrier = array_merge($Terrier['__vars'], array('__type' => 'Terrier'));
Pet__construct($a_terrier, 'Spike');
Terrier_setWeight($a_terrier, $var);
