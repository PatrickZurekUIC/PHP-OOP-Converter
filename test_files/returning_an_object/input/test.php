<?php
    include('TopLevelClass.php');
    include('SecondClass.php');

    $sc = new SecondClass();

    $obj = $sc->return_object();
