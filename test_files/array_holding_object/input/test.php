<?php
    include("SimpleClass.php");

    $obj[0][5][1] = new SimpleClass();

    echo "Val of obj's var is " . $obj[0][5][1]->var1 . "\n";
