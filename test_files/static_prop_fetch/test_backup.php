<?php

$testGlobalVar = 'hi';
$TopLevelClass_myStatic = 'topLevelStatic';
$TopLevelClass = array('__vars' => array('myStatic' => 'topLevelStatic'));
function Pet__construct(&$objInst, $name)
{
    echo 'Setting name to ' . $name . '
';
    $objInst['name'] = $name;
}
function Pet_ignoreMe()
{
    return 1;
}
$Pet_parentStaticVar = 1;
$Pet = array('__parent' => 'TopLevelClass', '__vars' => array_merge($TopLevelClass['__vars'], array('name' => null, 'parentStaticVar' => 1)));
function Dog_bark(&$objInst)
{
    global $Dog_parentStaticVar;
    global $Dog_myStatic;
    echo 'Woof. I am ' . $objInst['age'] . ' years old.
';
}
function Dog_setAge(&$objInst, $age)
{
    $objInst['age'] = $age;
    echo 'Parent static var: ' . $Dog_parentStaticVar . '
';
    // Above gets converted to Pet_parentStaticVar
    echo 'Parent static var: ' . parent::$parentStaticVar . '
';
    // Above gets converted to Pet_parentStaticVar
    echo 'Top Level Class static: ' . $Dog_myStatic . '
';
    // Above gets converted to TopLevelClass_myStatic
    echo 'Pet static var: ' . $TopLevelClass_myStatic;
    // Above gets converted to TopLevelCLass_myStatic;
    echo $testGlobalVar;
    global $testGlobalVar;
}
$Dog_testStaticVar = 10;
$Dog = array('__parent' => 'Pet', '__vars' => array_merge($Pet['__vars'], array('age' => null, 'testStaticVar' => 10)));
$a_pet = array_merge($Dog['__vars'], array('__type' => 'Dog'));
Pet__construct($a_pet, 'Spike');
Dog_setAge($a_pet, 10);
echo $Dog_testStaticVar . '
';
// Handle the case when the static var is not in the class on the LHS, but in its parent
echo $Pet_parentStaticVar . '
';
$Pet_parentStaticVar++;
echo $Pet_parentStaticVar . '
';
echo $Pet_parentStaticVar . '
';