<?php
    include("SimpleClass.php");
    include("OtherClass.php");

    echo "this is a test\n";

    $sc = new SimpleClass();
    $sc->printOtherClassVal();
