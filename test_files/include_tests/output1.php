<?php

include 'Pet.php';
function Dog_doesnothing(&$objInst)
{
    echo 'hi';
}
function Dog__construct()
{
    Pet__construct(func_get_args());
}
$Dog = array('__parent' => 'Pet', '__vars' => array_merge($Pet['__vars'], array()));
function Terrier_doesnothing2(&$objInst)
{
    echo 'hello';
}
function Terrier__construct()
{
    Dog__construct(func_get_args());
}
$Terrier = array('__parent' => 'Dog', '__vars' => array_merge($Dog['__vars'], array()));
echo 'Does nothing';
$a_terrier = array_merge($GLOBALS['Terrier']['__vars'], array('__type' => 'Terrier'));
Terrier__construct($a_terrier, 'Spike');