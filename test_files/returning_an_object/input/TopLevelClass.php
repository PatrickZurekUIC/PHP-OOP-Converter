<?php

  class TopLevelClass {

    function __construct() {
        echo "New object instantiated\n";
    }

    public static $non_arr_static = 5;
    public static $myStatic = array(0 => 'Zero', 1 => 'One');
  }

