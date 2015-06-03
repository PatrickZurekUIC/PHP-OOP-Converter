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
$Dog_COLLARED = true;
$Dog = array('__parent' => 'Pet', '__vars' => array_merge($Pet['__vars'], array('age' => null, 'weight' => null)));
$a_pet = array_merge($Dog['__vars'], array('__type' => 'Dog'));
Pet__construct($a_pet, 'Spike');
echo 'Is collared: ' . $Dog_COLLARED . '
';