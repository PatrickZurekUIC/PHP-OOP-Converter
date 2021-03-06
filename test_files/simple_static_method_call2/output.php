<?php

function Pet__construct(&$objInst, $name)
{
    echo 'Setting name to ' . $name . '
';
    $objInst['name'] = $name;
}
function Pet_staticFunc()
{
    echo 'This is a static function in Pet.
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
    Dog_staticFunc();
}
function Dog_staticFunc()
{
    echo 'This is a static function in Dog.
';
}
$Dog = array('__parent' => 'Pet', '__vars' => array_merge($Pet['__vars'], array('age' => null)));
$a_pet = array_merge($Dog['__vars'], array('__type' => 'Dog'));
Pet__construct($a_pet, 'Spike');
Dog_setAge($a_pet, 10);
Dog_staticFunc();