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
$a_pet = array_merge($Pet['__vars'], array('__type' => 'Pet'));
Pet__construct($a_pet, 'Spike');