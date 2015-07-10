<?php

function Items__construct(&$objInst)
{
}
function Items_test_func(&$objInst)
{
    $x = True ?: 'Test';
    echo "x is {$x}\n";
}
$Items = array('__vars' => array());