<?php
$items[] = "StepOne";
$items['two'] = "this is two";

echo "Items: " + $items[0];

echo "Reading from two: " . $items['two'];

function test_func($arg1) {
    func_call(func_get_args());
}

function func_call() {
    echo "This is func_call";
}
