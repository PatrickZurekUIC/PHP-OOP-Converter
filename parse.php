<?php 
  require 'vendor/autoload.php';
  use PhpParser\Node;
  use PhpParser\Node\Stmt;
  use PhpParser\Node\Expr;
  
  // This class is the main workhorse visitor of parse.php, visits all nodes in the program 
  class AllNodeVisitor extends PhpParser\NodeVisitorAbstract
  {
    public function leaveNode(Node $node) {
      // If the node is a class, create the global functions corresponding to the
      // class methods, convert occurances of this to objInst, and create the global
      // "Class" variable holding the information about the class (__vars, etc)
      if ($node instanceof Stmt\Class_) {
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
      }
    }

    // To convert a method call (Object->method() to Object__method()
    // we load a template that contains the code instead of building all 
    // that code manually using PHP-Parser.  We use placeholders in the 
    // template to designate what needs to be replaced
    // The code that this generates is what calls the correct method of an
    // object (whether it be the Object's own method, or one of its ancestors
    private function convert_method_call($node) {
      $code = file_get_contents('method-template.txt');
      // First add the object name to the template
      $code = str_replace('{obj}', $node->var->name, $code);
      // Next replace the method
      $code = str_replace('{method}', '_' . $node->name, $code);

      // Next, get the original arguments to the method call:
      $args = $node->args;
      $prettyPrinter = new PhpParser\PrettyPrinter\Standard;
      // By pretty printing it we convert it to a string so we can
      // seperate the arguments by commas
      $pp_args = $prettyPrinter->prettyPrint($args);
      $arg_lines = explode(PHP_EOL, $pp_args);
      // Add the arguments to a string separated by commas
      $arg_string = ",";
      foreach($arg_lines as $line) {
        $arg_string .= $line . ",";
      }
      // Remove the trailing comma
      $arg_string = rtrim($arg_string, ",");
      // ..and replace instances of {args} with the argument string 
      $code = str_replace('{args}', $arg_string, $code);
      // Parse the string and return it so that it can be added to the AST 
      $parser = new PhpParser\Parser(new PhpParser\Lexer);
      $stmts = $parser->parse($code);
      return $stmts;
    }

    private function convert_new_node($node) {
      // First, create the global obj_inst variable that gets passed to
      // the functions (originally the methods, now converted to functions)
      $stmts = $this->create_obj_inst($node);
      // Then create the nodes that call the proper constructor for that object
      $stmts = array_merge($stmts, $this->create_constructor($node));
      return $stmts;
    }

    private function create_obj_inst($node) {
      // Create the array merge function expression
      // Start by creating the arguments to it
      $class_var_name = new Node\Expr\Variable($node->expr->class->parts[0]);
      $arr_dim = new Node\Scalar\String("__vars");
      $first_arg_val = new Node\Expr\ArrayDimFetch($class_var_name, $arr_dim);
      
      $key = new Node\Scalar\String("__type");
      $value = new Node\Scalar\String($node->expr->class->parts[0]);
      $second_arg_items[] = new Expr\ArrayItem($value, $key);
      $second_arg_val = new Node\Expr\Array_($second_arg_items);

      $args[] = new Node\Arg($first_arg_val);
      $args[] = new Node\Arg($second_arg_val);
      $arr_merge = new Node\Name("array_merge");
      $expr = new Expr\FuncCall($arr_merge, $args); 
      $var = new Expr\Variable($node->var->name);
      // Finally, create the assignment expression and return it
      $objInst[] = new Expr\Assign($var, $expr);
      return $objInst;
    }

    private function create_constructor($node) {
      // See the comments in convert_method_call() for an explanation
      // of what is happening below since the methods are very similar
      // (this one is just for constructor calls)
      $code = file_get_contents('method-template.txt');
      $code = str_replace('{obj}', $node->var->name, $code);
      // Replace {method} with __construct
      $code = str_replace('{method}', '_' . '_construct', $code);
      $args = $node->expr->args;
      $prettyPrinter = new PhpParser\PrettyPrinter\Standard;
      $pp_args = $prettyPrinter->prettyPrint($args);
      $arg_lines = explode(PHP_EOL, $pp_args);
      $arg_string = ",";
      foreach($arg_lines as $line) {
        $arg_string .= $line . ",";
      }
      $arg_string = rtrim($arg_string, ",");
      $code = str_replace('{args}', $arg_string, $code);
      
      $parser = new PhpParser\Parser(new PhpParser\Lexer);
      $stmts = $parser->parse($code);
      return $stmts;
    }


    private function convert_class_node($node) {
      $factory = new PhpParser\BuilderFactory;
      $new_nodes = array();

      // Convert the class' methods to global functions
      $methods = $node->getMethods();
      foreach($methods as $method_node) {
        // Create the new function and name it
        if ($method_node->name == '__construct') {
          $new_node = $factory->function($node->name . $method_node->name);
        } else {
          $new_node = $factory->function($node->name . "_" . $method_node->name);
        }
        // Add the method parameters to the function signature
        $new_node = $new_node->addParam($factory->param("objInst")->makeByRef());
        foreach($method_node->params as $param) {
          $new_node = $new_node->addParam($param); 
        }
        // Traverse over the statements in the class methods and convert occurances
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

      // Now create the global variable for the class that holds its parent
      // and its member variables
      $class_var_name = new Node\Expr\Variable($node->name);

      $traverser = new PhpParser\NodeTraverser;
      $traverser->addVisitor(new ClassPropertyVisitorForVars);
      // Check if the node extends a class before trying to access its parent's name
      if ($node->extends instanceof Node\Name) {
        $value = new Node\Scalar\String($node->extends->toString());
        $key = new Node\Scalar\String("__parent");
        $array_items[] = new Node\Expr\ArrayItem($value, $key);

        $vars_key = new Node\Scalar\String("__vars");
        $func_call_name = new Node\Name("array_merge");

        // Set up the first argument to array_merge()
        $parent_var_name = new Node\Expr\Variable($node->extends->toString());
        $arr_dim = new Node\Scalar\String("__vars");
        $arg = new Node\Expr\ArrayDimFetch($parent_var_name, $arr_dim);
        $arr_merge_args[] = new Node\Arg($arg);

        // Set up the second argument to array_merge()
        // Loop over each method variable and create an array item
        $var_stmts = $traverser->traverse($node->stmts); 
     
        // Add the array items to the new array that's the second arg to array_merge
        //$arr_merge_args[] = new Node\Expr\Array_($merge_arg_array);
        $arg = new Node\Expr\Array_($var_stmts);
        $arr_merge_args[] = new Node\Arg($arg);

        $vars_value =  new Node\Expr\FuncCall($func_call_name, $arr_merge_args);
        $array_items[] = new Node\Expr\ArrayItem($vars_value, $vars_key);
      } else {
        // Node doesn't extend a class so just create the global class
        // variable with a vars item
        $key = new Node\Scalar\String("__vars");
        $var_stmts = $traverser->traverse($node->stmts);
        $value = new Node\Expr\Array_($var_stmts);
        $array_items[] = new Node\Expr\ArrayItem($value, $key);
      }

      $initial_array = new Node\Expr\Array_($array_items);
      $new_node = new Node\Expr\Assign($class_var_name, $initial_array); 
      $new_nodes[] = $new_node;

      // Now convert any class constants to global variables
      $traverser = new PhpParser\NodeTraverser;
      $traverser->addVisitor(new ClassPropertyVisitorForConstants($node->name));
      $class_constants = $traverser->traverse($node->stmts);
      $new_nodes = array_merge($new_nodes, $class_constants);

      return $new_nodes;
    }
  }
 
  // This class traverses over the statements in a class' method and
  // converts occurences of "this" to to use the objInst variable
  // Essentially $this->var becomes $objInst['var']
  class MethodStmtVisitor extends PhpParser\NodeVisitorAbstract
  {
    public function leaveNode(Node $node) {
      if($node instanceof Node\Expr\PropertyFetch) {
        $var_node = $node->var; 
        if ($var_node->name == "this") {
          $key_name = new Node\Scalar\String($node->name);
          $var_name = new Node\Expr\Variable("objInst");
          return new Node\Expr\ArrayDimFetch($var_name, $key_name);
        }
      }
    }
  }

  // This class traverses the properties of a class and is used to build 
  // the __vars part of the global "class" variable (i.e. it extracts the
  // public and protected variables of a class and returns it as an Array
  // so that it can be merged with the parent's __vars (if necessary)
  class ClassPropertyVisitorForVars extends PhpParser\NodeVisitorAbstract
  {
    public function leaveNode(Node $node) {
      if ($node instanceof Node\Stmt\Property) {
        // Don't assume variables of parent that were private
        if ($node->isPublic() || $node->isProtected()) {
          $prop_prop = $node->props[0];
          $vars_key = new Node\Scalar\String($prop_prop->name);
          // If the property value is not null, we can use it directly
          // else, create a "null" node and use that
          if ($prop_prop->default != null) {
            $vars_value = $prop_prop->default;      
          } else {
            $name = new Node\Name("null");
            $vars_value = new Node\Expr\ConstFetch($name);
          }
          $array_item = new Node\Expr\ArrayItem($vars_value, $vars_key);
          return $array_item;
        }      
      } elseif ($node instanceof Node\Stmt\ClassMethod) {
        return false;
      } elseif ($node instanceof Node\Stmt\ClassConst) {
        return false;
      }
    } 
  }

  class ClassPropertyVisitorForConstants extends PhpParser\NodeVisitorAbstract
  {
    private $this_class = null;
    public function __construct($this_class) {
      $this->this_class = $this_class;
    }
 
    public function leaveNode(Node $node) {
      if ($node instanceof Node\Stmt\ClassConst) {
        $name = $this->this_class . "_" . $node->consts[0]->name;
        $value = $node->consts[0]->value;
        $var = new Expr\Variable($name);
        $expr_assign = new Expr\Assign($var, $value);
        return $expr_assign;
      }
    }
  }

  if (sizeof($argv) != 3) {
    echo "Invalid number of arguments\n";
    exit(0);
  }
 
  $parser = new PhpParser\Parser(new PhpParser\Lexer);
  $traverser = new PhpParser\NodeTraverser;
  $prettyPrinter = new PhpParser\PrettyPrinter\Standard;
  $traverser->addVisitor(new AllNodeVisitor);
  $code = file_get_contents($argv[1]);
  try {
    $stmts = $parser->parse($code);
    if ($argv[2] == "nodedump") {
      $nodeDumper = new PhpParser\NodeDumper; 
      echo $nodeDumper->dump($stmts), "\n";
      exit(0);
    }
    $stmts = $traverser->traverse($stmts);
    $code = $prettyPrinter->prettyPrintFile($stmts);
    if ($argv[2] == "-") {
      echo $code . "\n";
    } else {
      file_put_contents($argv[2], $code); 
    }
  } catch (PhpParser\Error $e) {
    echo "Parse Error";
  }

?>
