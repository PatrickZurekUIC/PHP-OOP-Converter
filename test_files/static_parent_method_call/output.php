<?php

function GenericClass_staticFunc()
{
    echo 'This is a static function.
';
}
$GenericClass = array('__vars' => array());
function Pet__construct(&$objInst, $name)
{
    echo 'Setting name to ' . $name . '
';
    $objInst['name'] = $name;
}
$Pet = array('__parent' => 'GenericClass', '__vars' => array_merge($GenericClass['__vars'], array('name' => null)));
function Dog_bark(&$objInst)
{
    echo 'Woof. I am ' . $objInst['age'] . ' years old.
';
}
function Dog_setAge(&$objInst, $age)
{
    $objInst['age'] = $age;
}
function Dog_testStaticFunc(&$objInst)
{
    GenericClass_staticFunc();
}
$Dog = array('__parent' => 'Pet', '__vars' => array_merge($Pet['__vars'], array('age' => null)));
$a_pet = array_merge($Dog['__vars'], array('__type' => 'Dog'));
Pet__construct($a_pet, 'Spike');
Dog_testStaticFunc($a_pet);