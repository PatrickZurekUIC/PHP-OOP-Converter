<?php
require 'vendor/autoload.php';
use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\Node\Expr;

// This class is the main workhorse of parse.php, visits all nodes in the program
class AllNodeVisitor extends PhpParser\NodeVisitorAbstract
{
    private $methods_parents = array();
    private $globals = array();
    private $current_class;
    private $current_method;

    public function enterNode(Node $node) {
        if ($node instanceof Stmt\Class_) {
            $this->current_class = $node->name;
            if ($node->extends instanceof Node\Name) {
                $parent = $node->extends->toString();
            } else {
                $parent = null;
            }
            $this->methods_parents[$node->name] = array();
            $this->methods_parents[$node->name]['parent'] = $parent;
            $this->methods_parents[$node->name]['globals'] = array();
            $this->methods_parents[$node->name]['methods'] = array();
            $methods = $node->getMethods();
            foreach($methods as $method_node) {
                $this->methods_parents[$node->name]['methods'][] = $method_node->name;
            }

            $this->methods_parents[$node->name]['statics'] = array();
            foreach($node->stmts as $stmt) {
                if ($stmt instanceof Stmt\Property && $stmt->isStatic()) {
                    $this->methods_parents[$node->name]['statics'][] = $stmt->props[0]->name;
                }
            }
        } elseif ($node instanceof Expr\StaticPropertyFetch) {
            return $this->convert_static_prop_fetch($node);
        } elseif ($node instanceof Stmt\ClassMethod) {
            //echo "Current method is: " . $this->current_method . ". Setting it to ". $node->name . "\n";
            $this->current_method = $node->name;
        }
    }

    public function leaveNode(Node $node) {
        // If the node is a class, create the global functions corresponding to the
        // class methods, convert occurrences of this to objInst, and create the global
        // "Class" variable holding the information about the class (__vars, etc)
        if ($node instanceof Stmt\Class_) {
            // EXPERIMENTAL:
            $this->current_class = null;
            return $this->convert_class_node($node);
        } elseif ($node->expr instanceof Expr\New_ ) {
            // If the node is creating a new object with "new"
            // convert it to create an objInst variable representing the object
            // and call the correct constructor function
            return $this->convert_new_node($node);
        } elseif ($node instanceof Expr\MethodCall) {
            // The node is a call to an object's method.  Convert it to call the
            // correct global function of the object
            return $this->convert_method_call($node);
        } elseif ($node instanceof Expr\ClassConstFetch) {
            // The node fetches a class constant.  Convert it to
            // Classname_variablename
            $name = $node->class->parts[0];
            $name = $name . "_" . $node->name;
            return new Expr\Variable($name);
        } elseif ($node instanceof Expr\StaticCall) {
            // Node is a call to a static class method.  Convert Class::method()
            // or parent::method() to the correct function Class_method();
            return $this->convert_static_call($node);
        } elseif ($node instanceof Stmt\ClassMethod) {
            // EXPERIMENTAL
            //echo "Current method is: " . $this->current_method . ". Setting it to NULL\n";
            $this->current_method = null;
        }
    }

    private function convert_static_prop_fetch($node) {
        if ($node->class->parts[0] == "self") {
          $class = $this->current_class;
          $class_statics = $this->methods_parents[$class]['statics'];
          $static = $node->name;
          if (!in_array($static, $class_statics)) {
              while (true) {
                  $parent = $this->methods_parents[$class]['parent'];
                  $class = $parent;
                  if (in_array($static, $this->methods_parents[$parent]['statics'])){
                      break;
                  }
              }
          }

          $name = $class . "_" . $static;
          $variable = new Expr\Variable($name);

          $this->methods_parents[$this->current_class][$this->current_method][] = $name;

          return $variable;
        } elseif ($node->class->parts[0] == "parent") {
          $class = $this->current_class;
          $class_statics = $this->methods_parents[$class]['statics'];
          $static = $node->name;

          while (true) {
              $parent = $this->methods_parents[$class]['parent'];
              $class = $parent;
              if (in_array($static, $this->methods_parents[$parent]['statics'])){
                  break;
              }
          }

          $name = $class . "_" . $static;
          $variable = new Expr\Variable($name);

          $this->methods_parents[$this->current_class][$this->current_method][] = $name;

          return $variable;
        } else {
            $class = $node->class->parts[0];
            $class_statics = $this->methods_parents[$class]['statics'];
            $static = $node->name;
            if (!in_array($static, $class_statics)) {
                while (true) {
                    $parent = $this->methods_parents[$class]['parent'];
                    $class = $parent;
                    if (in_array($static, $this->methods_parents[$parent]['statics'])){
                        break;
                    }
                }
            }

            $name = $class . "_" . $static;
            $variable = new Expr\Variable($name);

            if ($this->current_class != null && $this->current_method != null) {
              $this->methods_parents[$this->current_class][$this->current_method][] = $name;
              //echo "Adding " . $name . " to " . $this->current_class . "::" . $this->current_method;
            }

            return $variable;
        }
    }

    private function convert_static_call($node) {
        global $pp_parent_array;
        global $pp_static_class_methods;
        $method = $node->name;
        $class = $node->class->parts[0];
        if ($class == "parent") {
            $class = $pp_parent_array[$this->current_class];
            while (!in_array($method, $pp_static_class_methods[$class])) {
                $class = $pp_parent_array[$class];
            }
            $args = $node->args;
            $name = $class . "_" . $method;
            $name = new Node\Name($name);
            $func_call_stmt = new Expr\FuncCall($name, $args);
            return $func_call_stmt;
        } elseif ($class == "self") {
            $class = $this->current_class;
            while (!in_array($method, $pp_static_class_methods[$class])) {
                $class = $pp_parent_array[$class];
            }
            $args = $node->args;
            $name = $class . "_" . $method;
            $name = new Node\Name($name);
            $func_call_stmt = new Expr\FuncCall($name, $args);
            return $func_call_stmt; 
        } else {
            // The static function call is outside a class method of
            // the form Class::method()
            $class = $node->class->parts[0];
            $methods = $pp_static_class_methods[$class];
            while (!in_array($method, $pp_static_class_methods[$class])) {
                $class = $pp_parent_array[$class];
            }
            $args = $node->args;
            $name = $class . "_" . $method;
            $name = new Node\Name($name);
            $func_call_stmt = new Expr\FuncCall($name, $args);
            return $func_call_stmt; 
        }
    }

    // Converts a node from the form $obj->method() to Class_method()
    // and prepends the objInst variable to the arguments list
    private function convert_method_call($node) {
        // Find the correct method to call, either the class' own method or if it doesn't exist,
        // then one of its parents
        global $pp_class_methods;
        global $pp_parent_array;
        global $obj_class_map;
        
        $method = $node->name;

        $class = $obj_class_map[$node->var->name];
        echo "Looking for method: $method in class $class\n";
        while (!in_array($method, $pp_class_methods[$class])) {
            $class = $pp_parent_array[$class];
        }
        // Found the right class now create the method
        $func_call_name = $class . "_" . $method;
        $name = new Node\Name($func_call_name);
        $args = $node->args;

        $obj_inst_var = new Expr\Variable($node->var->name);
        $obj_inst_arg = new Node\Arg($obj_inst_var);
        array_unshift($args, $obj_inst_arg);
        $func_call_stmt = new Expr\FuncCall($name, $args);

        return $func_call_stmt;

    }

    // This function creates the global objInst variable representing a concrete
    // instance of the class and then it determines and calls the correct
    // constructor
    private function convert_new_node($node) {
        // First, create the global obj_inst variable
        // Then create the constructor call
        $stmts[] = $this->create_obj_inst($node);
        $stmts[] = $this->create_constructor($node);
        return $stmts;
    }

    // Creates the concrete instance of the class.  It merges the member
    // variables into the objInst with the type of the object.
    private function create_obj_inst($node) {
        // Create the array merge function expression
        // Start by creating the arguments to it

        // Replaced the below with an access to the GLOBALS superglobal
        /*$class_var_name = new Expr\Variable($node->expr->class->parts[0]);
        $arr_dim = new Node\Scalar\String("__vars");
        $first_arg_val = new Expr\ArrayDimFetch($class_var_name, $arr_dim);*/

        $globals_var = new Expr\Variable("GLOBALS");
        $first_arr_dim = new Node\Scalar\String($node->expr->class->parts[0]);
        $second_arr_dim = new Node\Scalar\String("__vars");
        $inner_dim_fetch = new Expr\ArrayDimFetch($globals_var, $first_arr_dim);
        $first_arg_val = new Expr\ArrayDimFetch($inner_dim_fetch, $second_arr_dim);

        $key = new Node\Scalar\String("__type");
        $value = new Node\Scalar\String($node->expr->class->parts[0]);
        $second_arg_items[] = new Expr\ArrayItem($value, $key);
        $second_arg_val = new Expr\Array_($second_arg_items);

        $args[] = new Node\Arg($first_arg_val);
        $args[] = new Node\Arg($second_arg_val);
        $arr_merge = new Node\Name("array_merge");
        $expr = new Expr\FuncCall($arr_merge, $args);
        $var = new Expr\Variable($node->var->name);
        // Finally, create the assignment expression and return it
        $objInst = new Expr\Assign($var, $expr);
        return $objInst;
    }

    private function create_constructor($node) {

        // Find what class this object belongs to
        $class = $node->expr->class->parts[0];

        // Find the correct constructor to call, either the class' own method or if it doesn't exist,
        // then one of its parents
        global $pp_class_methods;
        global $pp_parent_array;
        global $obj_class_map;
        $method = "__construct";
        $class = $obj_class_map[$node->var->name];
        echo "Checking class: $class for constructor\n";
        while (!in_array($method, $pp_class_methods[$class])) {
            print_r($pp_class_methods[$class]);
            $class = $pp_parent_array[$class];
            echo "Constructor not found, now checking class: $class\n";
        }
        // Found the right class now create the method


        // We know the correct constructor function to call,
        // now construct it and return it
        $func_call_name = $class . $method;
        $name = new Node\Name($func_call_name);
        $args = $node->expr->args;
        // Create the objInst variable
        $obj_inst_var = new Expr\Variable($node->var->name);
        $obj_inst_arg = new Node\Arg($obj_inst_var);
        // Add obj_inst to front of arguments array
        array_unshift($args, $obj_inst_arg);

        $func_call_stmt = new Expr\FuncCall($name, $args);
        return $func_call_stmt;
    }

    // This function converts an entire class when it is encountered.
    // 1. Convert the class methods to global functions of the
    //    form Classname_methodname() adding objInst to the method signature.
    // 2. Look for occurrences of "this" and convert it to objInst.
    // 3. Create the global "Class" variable that holds its parent and it's
    //    member variables.
    private function convert_class_node($node) {
        $factory = new PhpParser\BuilderFactory;
        $new_nodes = array();
        $explicit_constructor = False;

        // Convert the class' methods to global functions
        $methods = $node->getMethods();

        foreach($methods as $method_node) {

            // This is used below when constructing the shim functions
            $method_names[] = $method_node->name;

            // Create the new function and name it
            if ($method_node->name == '__construct') {
                $explicit_constructor = True;
                $new_node = $factory->function($node->name . $method_node->name);
                $argsParam = new PhpParser\Builder\Param("args");
                $new_node = $new_node->addParam($argsParam);

                $name = "objInst";
                
                $arr_dim = new Node\Scalar\LNumber(0);
                $array_name = new Expr\Variable("args");
                $array_fetch = new Expr\ArrayDimFetch($array_name, $arr_dim);

                $var = new Expr\Variable($name);
                $objInst = new Expr\Assign($var, $array_fetch);

                $new_node = $new_node->addStmt($objInst);

                $i = 1;
                foreach($method_node->params as $param) {
                    $arr_dim = new Node\Scalar\LNumber($i++);
                    $array_name = new Expr\Variable("args");
                    $array_fetch = new Expr\ArrayDimFetch($array_name, $arr_dim);

                    $var = new Expr\Variable($param->name);
                    $new_var_expr = new Expr\Assign($var, $array_fetch);
                    $new_node = $new_node->addStmt($new_var_expr);
                }

                $traverser = new PhpParser\NodeTraverser;
                $traverser->addVisitor(new MethodStmtVisitor);
                $stmts = $traverser->traverse($method_node->stmts);

                // Add the statements from the original method to the function
                foreach($stmts as $stmt) {
                    $new_node = $new_node->addStmt($stmt);
                }

                $new_node = $new_node->getNode();
                $new_nodes[] = $new_node;
            } else { 

                $new_node = $factory->function($node->name . "_" . $method_node->name);
            
                // Add the method parameters to the function signature
                // if it is not a static method (we don't need objInst in this case)
                if (!$method_node->isStatic()) {
                    $new_node = $new_node->addParam($factory->param("objInst")->makeByRef());
                }
                foreach($method_node->params as $param) {
                    $new_node = $new_node->addParam($param);
                }

                // Add "global $var" statements for any static variables we encountered
                // in the original method
                if (array_key_exists($method_node->name, $this->methods_parents[$node->name])) {
                    echo "Array key exists for : " . $method_node->name;
                    foreach ($this->methods_parents[$node->name][$method_node->name] as $global) {
                        $var = new Expr\Variable($global);
                        $stmts[] = new Stmt\Global_(array($var));
                    }
                    foreach($stmts as $stmt) {
                      $new_node = $new_node->addStmt($stmt);
                    }
                }

                // Traverse over the statements in the class methods and convert occurrences
                // of "this" to use objInst
                $traverser = new PhpParser\NodeTraverser;
                $traverser->addVisitor(new MethodStmtVisitor);
                $stmts = $traverser->traverse($method_node->stmts);

                // Add the statements from the original method to the function
                foreach($stmts as $stmt) {
                    $new_node = $new_node->addStmt($stmt);
                }
                $new_node = $new_node->getNode();
                $new_nodes[] = $new_node;
            }
        }

        // Now create the global variable for the class that holds its parent
        // and its member variables
        $class_var_name = new Expr\Variable($node->name);

        $static_vars = array();
        $member_vars = array();
        $class_consts = array();
        // Check if the node extends a class before trying to access its parent's name
        if ($node->extends instanceof Node\Name) {
            $value = new Node\Scalar\String($node->extends->toString());
            $key = new Node\Scalar\String("__parent");
            $array_items[] = new Expr\ArrayItem($value, $key);

            // Set up the first argument to array_merge()
            $parent_var_name = new Expr\Variable($node->extends->toString());
            $arr_dim = new Node\Scalar\String("__vars");
            $arg = new Expr\ArrayDimFetch($parent_var_name, $arr_dim);
            $arr_merge_args[] = new Node\Arg($arg);

            foreach($node->stmts as $stmt) {
                if ($stmt instanceof Stmt\ClassConst) {
                    $name = $node->name . "_" . $stmt->consts[0]->name;
                    $value = $stmt->consts[0]->value;
                    $var = new Expr\Variable($name);
                    $class_consts[] = new Expr\Assign($var, $value);
                } elseif ($stmt instanceof Node\Stmt\Property) {
                    if ($stmt->isStatic()) {
                        $prop = $stmt->props[0];
                        $name = $node->name . "_" . $prop->name;
                        $var = new Expr\Variable($name);
                        if ($prop->default == null) {
                            $null = new Node\Name("null");
                            $null_const = new Expr\ConstFetch($null);
                            $static_vars[] = new Expr\Assign($var, $null_const);
                        } else {
                            $static_vars[] = new Expr\Assign($var, $prop->default);
                        }
                    }
                    // Don't assume variables of parent that were private
                    if ($stmt->isPublic() || $stmt->isProtected()) {
                        $prop_prop = $stmt->props[0];
                        $vars_key = new Node\Scalar\String($prop_prop->name);
                        // If the property value is not null, we can use it directly
                        // else, create a "null" node and use that
                        if ($prop_prop->default != null) {
                            $vars_value = $prop_prop->default;
                        } else {
                            $name = new Node\Name("null");
                            $vars_value = new Expr\ConstFetch($name);
                        }
                        $member_vars[] = new Expr\ArrayItem($vars_value, $vars_key);
                    }
                }
            }

            // Add the array items to the new array that's the second arg to array_merge
            $arg = new Expr\Array_($member_vars);
            $arr_merge_args[] = new Node\Arg($arg);

            $func_call_name = new Node\Name("array_merge");
            $vars_value = new Expr\FuncCall($func_call_name, $arr_merge_args);
            $vars_key = new Node\Scalar\String("__vars");
            $array_items[] = new Expr\ArrayItem($vars_value, $vars_key);
        } else {
            // Node doesn't extend a class so just create the global class
            // variable with a vars item
            foreach($node->stmts as $stmt) {
                if ($stmt instanceof Stmt\ClassConst) {
                    $name = $node->name . "_" . $stmt->consts[0]->name;
                    $value = $stmt->consts[0]->value;
                    $var = new Expr\Variable($name);
                    $class_consts[] = new Expr\Assign($var, $value);
                } elseif ($stmt instanceof Stmt\Property) {
                    if ($stmt->isStatic()) {
                        $prop = $stmt->props[0];
                        $name = $node->name . "_" . $prop->name;
                        $var = new Expr\Variable($name);
                        if ($prop->default == null) {
                            $null = new Name("null");
                            $null_const = new Expr\ConstFetch($null);
                            $static_vars[] = new Expr\Assign($var, $null_const);
                        } else {
                            $static_vars[] = new Expr\Assign($var, $prop->default);
                        }
                    }
                    // Don't assume variables of parent that were private
                    if ($stmt->isPublic() || $stmt->isProtected()) {
                        $prop_prop = $stmt->props[0];
                        $vars_key = new Node\Scalar\String($prop_prop->name);
                        // If the property value is not null, we can use it directly
                        // else, create a "null" node and use that
                        if ($prop_prop->default != null) {
                            $vars_value = $prop_prop->default;
                        } else {
                            $name = new Node\Name("null");
                            $vars_value = new Expr\ConstFetch($name);
                        }
                        $member_vars[] = new Expr\ArrayItem($vars_value, $vars_key);
                    }
                }
            }

            $value = new Expr\Array_($member_vars);
            $vars_key = new Node\Scalar\String("__vars");
            $array_items[] = new Expr\ArrayItem($value, $vars_key);
        }

        $new_nodes = array_merge($new_nodes, $static_vars);
        $new_nodes = array_merge($new_nodes, $class_consts);
        $initial_array = new Expr\Array_($array_items);
        $new_node = new Expr\Assign($class_var_name, $initial_array);
        $new_nodes[] = $new_node;

        return $new_nodes;
    }

    private function create_shim_function($shim_method, $child, $parent) {
        $factory = new PhpParser\BuilderFactory;
        $new_node = $factory->function($child . '_' . $shim_method);

        $name = $parent . "_" . $shim_method;
        $name = new Node\Name($name);

        $get_args_func = new Node\Name("func_get_args");
        $args = array();
        $arg = new Expr\FuncCall($get_args_func, $args);
        $args[] = new Node\Arg($arg);
        
        $func_call_stmt = new Expr\FuncCall($name, $args);
 
        $new_node = $new_node->addStmt($func_call_stmt);
        $new_node = $new_node->getNode();
        return $new_node;
    }

}


// This class traverses over the statements in a class' method and
// converts occurrences of "this" to to use the objInst variable
// Essentially $this->var becomes $objInst['var']
class MethodStmtVisitor extends PhpParser\NodeVisitorAbstract
{
    public function leaveNode(Node $node) {
        if ($node instanceof Expr\PropertyFetch) {
            $var_node = $node->var;
            if ($var_node->name == "this") {
                $key_name = new Node\Scalar\String($node->name);
                $var_name = new Expr\Variable("objInst");
                return new Expr\ArrayDimFetch($var_name, $key_name);
            }
        } elseif ($node instanceof Expr\StaticPropertyFetch) {

        }
    }
}


class AllNodePreprocessor extends PhpParser\NodeVisitorAbstract
{
    public function leaveNode(Node $node) {
        global $pp_parent_array;
        global $pp_class_methods;
        global $pp_static_class_methods;
        global $obj_class_map;

        if ($node->expr instanceof Expr\New_) {
            $val = $node->expr->class->parts[0];
            $key = $node->var->name;
            echo "Adding $key to $val\n";
            $obj_class_map[$node->var->name] = $node->expr->class->parts[0];
        } else if ($node instanceof Stmt\Class_) {
            global $parent_array;
            if ($node->extends instanceof Node\Name) {
                $parent = $node->extends->toString();
            } else {
                $parent = null;
            }
            $pp_parent_array[$node->name] = $parent; 
            $methods = $node->getMethods();
            foreach($methods as $method_node) {        
                if ($method_node->isStatic()) {
                    $pp_static_class_methods[$node->name][] = $method_node->name;
                }
                if ($method_node->isPublic() || $method_node->isProtected) {
                    $pp_class_methods[$node->name][] = $method_node->name;
                } 
            }
        }
    }
}


////////////////////////
// Begin "main"
////////////////////////    
if (sizeof($argv) < 1 || sizeof($argv) > 3) {
    echo "Invalid number of arguments\n";
    exit(0);
}

$parser = new PhpParser\Parser(new PhpParser\Lexer);

$preprocessor = new PhpParser\NodeTraverser;
$preprocessor->addVisitor(new AllNodePreprocessor);

$traverser = new PhpParser\NodeTraverser;
$prettyPrinter = new PhpParser\PrettyPrinter\Standard;
$traverser->addVisitor(new AllNodeVisitor);

if (sizeof($argv == 2) && $argv[1] == 'nodedump') {
    $nodedump = True;
    $in_dir = $argv[2];
    echo "here";
}

// This is the added code to check for user-specified input and output directories
// ----------------------------------------------------------------------------
else if (sizeof($argv) >= 2) {
	if (preg_match('(/?[a-zA-Z_0-9]+/$)', $argv[1])) {
		$in_dir = $argv[1];
	} else
		$in_dir = "input/";

	if (sizeof($argv) == 3) {
		if (preg_match('(/?[a-zA-Z_0-9]+/$)', $argv[2]))
			$out_dir = $argv[2];
		else
			$out_dir = "output/";
	} else
		$out_dir = "output/";

} else {
	$in_dir = "input/";
	$out_dir = "output/";
}


// Checks existence of output directory and creates it if it does not exist
if (!file_exists($out_dir) && !$nodedump) {
	echo "Path: " . $out_dir . "does not exist. Create it? (Y/N): ";
	$ans = readline();
	if (preg_match('/^(Y|y)$/', $ans)) {
		if (!mkdir($out_dir, 0777, true)) {
			echo "Could not create directory.";
			exit(0);
		}  
	} else {
			echo "Quitting...";
			exit(0);
	}
}
// ------------------------------------------------------------------------------

$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($in_dir));
$files = new RegexIterator($files, '/\.php$/');

if ($nodedump) {
    $nodeDumper = new PhpParser\NodeDumper;
    foreach ($files as $file) {
        $code = file_get_contents($file);
        echo "Dumping nodes for $file\n";
        $stmts = $parser->parse($code);
        echo $nodeDumper->dump($stmts), "\n";
    }
    exit(0);
}

foreach ($files as $file) {
    echo "Scanning file: $file\n";
    $code = file_get_contents($file);
     try {
        $stmts = $parser->parse($code);
        $preprocessor->traverse($stmts);
    } catch (PhpParser\Error $e) {
        echo "Parse Error";
    }
}

foreach ($files as $file) {
    $code = file_get_contents($file);
    try {
        $stmts = $parser->parse($code);
        $stmts = $traverser->traverse($stmts);
        $code = $prettyPrinter->prettyPrintFile($stmts);
		$f_out = substr_replace($file->getPathname(), $out_dir, 0, strlen($in_dir)); // Current output file
		$f_out_dir = substr($f_out, 0, strrpos($f_out, '/'));                        // Current output directory
		if (!file_exists($f_out_dir)) {                                              // Check directories and create, as necessary
			if (!mkdir($f_out_dir, 0777, true)) {
				echo "Could not create directory.";
				exit(0);
			}
		}
        file_put_contents(substr_replace($file->getPathname(), $out_dir, 0, strlen($in_dir)), $code);
    } catch (PhpParser\Error $e) {
        echo "Parse Error";
    }
}

?>
