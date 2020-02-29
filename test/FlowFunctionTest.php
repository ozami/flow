<?php
require_once __DIR__ . "/sample.php";

use Coroq\FlowFunction;

class FlowFunctionTest extends PHPUnit_Framework_TestCase {
  public function testPassingNull() {
    $flowFunction = new FlowFunction(null);
    $arguments = ["x" => 1];
    $result = $flowFunction($arguments);
    $this->assertSame($arguments, $result);
  }
  
  public function testPassingEmptyFunction() {
    $flowFunction = new FlowFunction("emptyFunction");
    $arguments = ["x" => 1];
    $result = $flowFunction($arguments);
    $this->assertSame($arguments, $result);
  }

  public function testPassingEmptyClosure() {
    $flowFunction = new FlowFunction(function() {});
    $arguments = ["x" => 1];
    $result = $flowFunction($arguments);
    $this->assertSame($arguments, $result);
  }

  public function testPassingEmptyMethod() {
    $object = new EmptyClass();
    $flowFunction = new FlowFunction([$object, "emptyMethod"]);
    $arguments = ["x" => 1];
    $result = $flowFunction($arguments);
    $this->assertSame($arguments, $result);
  }

  public function testPassingEmptyStaticMethod() {
    $object = new EmptyClass();
    $flowFunction = new FlowFunction([$object, "emptyStaticMethod"]);
    $arguments = ["x" => 1];
    $result = $flowFunction($arguments);
    $this->assertSame($arguments, $result);

    $flowFunction = new FlowFunction(["EmptyClass", "emptyStaticMethod"]);
    $result = $flowFunction($arguments);
    $this->assertSame($arguments, $result);

    $flowFunction = new FlowFunction("EmptyClass::emptyStaticMethod");
    $result = $flowFunction($arguments);
    $this->assertSame($arguments, $result);
  }

  public function testPassingEmptyCallableObject() {
    $object = new EmptyCallableClass();
    $flowFunction = new FlowFunction($object);
    $arguments = ["x" => 1];
    $result = $flowFunction($arguments);
    $this->assertSame($arguments, $result);
  }

  public function testPassingEmptyFlowFunction() {
    $flowFunction = new FlowFunction(new FlowFunction(function() {
    }));
    $arguments = ["x" => 1];
    $result = $flowFunction($arguments);
    $this->assertSame($arguments, $result);
  }

  public function testArgumentsBinding() {
    $test = $this;
    $flowFunction = new FlowFunction(function($x, $y, $arguments) use ($test) {
      $test->assertSame(1, $x);
      $test->assertSame(2, $y);
      $test->assertSame(["x" => 1, "y" => 2], $arguments);
    });
    $result = $flowFunction(["x" => 1, "y" => 2]);
  }

  public function testNullArgumentsBinding() {
    $test = $this;
    $flowFunction = new FlowFunction(function($x, $y, $arguments) use ($test) {
      $test->assertSame(null, $x);
      $test->assertSame(null, $y);
      $test->assertSame([], $arguments);
    });
    $result = $flowFunction([]);
  }

  public function testArgumentsBindingWithNestedFlowFunctions() {
    $test = $this;
    $flowFunction = new FlowFunction(new FlowFunction(function($x, $y, $arguments) use ($test) {
      $test->assertSame(1, $x);
      $test->assertSame(2, $y);
      $test->assertSame(["x" => 1, "y" => 2], $arguments);
    }));
    $result = $flowFunction(["x" => 1, "y" => 2]);
  }

  public function testAssignment() {
    $flowFunction = new FlowFunction(function($x) {
      return ["x" => 1];
    });
    $result = $flowFunction([]);
    $this->assertSame(["x" => 1], $result);
  }

  public function testOverwrite() {
    $flowFunction = new FlowFunction(function($x) {
      return ["x" => 2];
    });
    $result = $flowFunction(["x" => 1]);
    $this->assertSame(["x" => 2], $result);
  }

  public function testOverwriteWithNull() {
    $flowFunction = new FlowFunction(function($x) {
      return ["x" => null];
    });
    $result = $flowFunction(["x" => 1]);
    $this->assertSame(["x" => null], $result);
  }

  public function testAdd() {
    $flowFunction = new FlowFunction(function($x, $y) {
      return ["x" => 1];
    });
    $result = $flowFunction(["y" => 2]);
    $this->assertSame(["x" => 1, "y" => 2], $result);
  }
  
  public function testFlowFunctionThrowsExceptionWhenStringReturned() {
    try {
      $flowFunction = new FlowFunction(function() {
        return "test";
      });
      $flowFunction();
    }
    catch (\DomainException $e) {
      $this->assertRegExp("#^.+ defined in .+\\([0-9]+\\)#", $e->getMessage());
    }
  }
}
