<?php

function Dog_bark(&$objInst)
{
    echo $objInst['name'] . ' says Woof
';
}
function Dog__construct()
{
    Pet__construct(func_get_args());
}
$Dog = array('__parent' => 'Pet', '__vars' => array_merge($Pet['__vars'], array()));