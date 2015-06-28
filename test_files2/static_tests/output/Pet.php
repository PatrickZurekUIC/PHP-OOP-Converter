<?php

function Pet_statMethod()
{
    echo 'This is a stat method';
}
function Pet__construct()
{
    __construct(func_get_args());
}
$Pet = array('__vars' => array());