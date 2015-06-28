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
$Pet = array('__vars' => array('name' => null));
function Dog_bark(&$objInst)
{
    echo 'Woof. I am ' . $objInst['age'] . ' years old.
';
}
function Dog_setAge(&$objInst, $age)
{
    $objInst['age'] = $age;
}
$Dog_should_be_collared = true;
$Dog_test_static2 = null;
$Dog = array('__parent' => 'Pet', '__vars' => array_merge($Pet['__vars'], array('age' => null, 'weight' => 10)));
$a_pet = array_merge($Dog['__vars'], array('__type' => 'Dog'));
Pet__construct($a_pet, 'Spike');
echo 'Should be collared: ' . $Dog_should_be_collared . '
';
$Dog_test_static2 = 5;