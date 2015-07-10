<?php

function TestClass__construct($args)
{
    $objInst = $args[0];
}
function TestClass_set_fmap(&$objInst)
{
    foreach (array('paperSummary', 'commentsToAuthor', 'commentsToPC', 'commentsToAddress', 'weaknessOfPaper', 'strengthOfPaper', 'textField7', 'textField8') as $fid) {
        $normal_var = array_merge($GLOBALS['ReviewField']['__vars'], array('__type' => 'ReviewField'));
        ReviewField__construct($normal_var, $fid);
    }
}
function TestClass_get_fmap(&$objInst)
{
    return $objInst['fmap'];
}
$TestClass = array('__vars' => array('fmap' => array(), 'test_this' => null, 'property' => array()));
function ReviewField__construct($args)
{
    $objInst = $args[0];
    $fid = $args[1];
    $objInst['fid'] = $fid;
}
function ReviewField_getField(&$objInst)
{
    return $objInst['fid'];
}
$ReviewField = array('__vars' => array());