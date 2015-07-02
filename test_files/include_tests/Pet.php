<?php

function Pet__construct($args)
{
    $objInst = $args[0];
    $name = $args[1];
    $objInst['name'] = $name;
    echo 'And this is a statement.
';
}
$Pet = array('__vars' => array());
