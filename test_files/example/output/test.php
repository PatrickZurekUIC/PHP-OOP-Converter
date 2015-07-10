<?php

function TestClass__construct(&$objInst, $prop)
{
    $objInst['prop'] = $prop;
}
$TestClass = array('__vars' => array('prop' => 5));