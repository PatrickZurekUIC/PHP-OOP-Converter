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
$Pet = array('__vars' => array('name' => null));
function Dog_bark(&$objInst)
{
    echo $objInst['name'] . ' says Woof
';
}
$Dog = array('__parent' => 'Pet', '__vars' => array_merge($Pet['__vars'], array()));
function Terrier_setWeightAndEat(&$objInst, $weight)
{
    global $Terrier_my_static;
    Pet_eat();
    echo 'Accessing static var using \'self.\'  Var is: ' . $Terrier_my_static . '
';
    //echo "Trying to access a static function: " . self::static_func();
    return 'Test';
}
function Terrier_static_func($test)
{
    echo 'This is a static function.
';
}
$Terrier_my_static = 'foo';
$Terrier = array('__parent' => 'Dog', '__vars' => array_merge($Dog['__vars'], array('weight' => null)));
$var = 30;
$a_terrier = array_merge($Terrier['__vars'], array('__type' => 'Terrier'));
Pet__construct($a_terrier, 'Spike');
Terrier_setWeightAndEat($a_terrier, $var);
echo 'Got string: ' . Pet_testFunc($a_terrier) . '
';
echo 'Static var is: ' . $Terrier_my_static . '
';
Terrier_static_func('test');
