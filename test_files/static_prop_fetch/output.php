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
$Pet_petStaticVar = 1;
$Pet = array('__parent' => 'TopLevelClass', '__vars' => array_merge($TopLevelClass['__vars'], array('name' => null, 'petStaticVar' => 1)));
function Dog_bark(&$objInst)
{
    echo 'Woof. I am ' . $objInst['age'] . ' years old.
';
}
function Dog_setAge(&$objInst, $age)
{
    echo 'Woof. I am ' . $objInst['age'] . ' years old.
';
    global $Pet_petStaticVar;
    global $Pet_petStaticVar;
    global $TopLevelClass_myStatic;
    global $TopLevelClass_myStatic;
    global $TopLevelClass_myStatic;
    $objInst['age'] = $age;
    echo 'Parent static var: ' . $Pet_petStaticVar . '
';
    // Above gets converted to Pet_petStaticVar
    echo 'Parent static var: ' . $Pet_petStaticVar . '
';
    // Above gets converted to Pet_petStaticVar
    echo 'Top Level Class static: ' . $TopLevelClass_myStatic . '
';
    // Above gets converted to TopLevelClass_myStatic
    echo 'Pet static var: ' . $TopLevelClass_myStatic;
    // Above gets converted to TopLevelCLass_myStatic;
    echo 'TopLevelClass_myStatic: ' . $TopLevelClass_myStatic . '
';
    // Above gets converted to TopLevelClass_myStatic
    global $testGlobalVar;
    echo $testGlobalVar;
}
$Dog_testStaticVar = 10;
$Dog = array('__parent' => 'Pet', '__vars' => array_merge($Pet['__vars'], array('age' => null, 'testStaticVar' => 10)));
$a_pet = array_merge($Dog['__vars'], array('__type' => 'Dog'));
Pet__construct($a_pet, 'Spike');
Dog_setAge($a_pet, 10);
echo $Dog_testStaticVar . '
';
// Handle the case when the static var is not in the class on the LHS, but in its parent
echo $Pet_petStaticVar . '
';
$Pet_petStaticVar++;
echo $Pet_petStaticVar . '
';
echo $Pet_petStaticVar . '
';