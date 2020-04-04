<?php
require_once __DIR__ . "/sample.php";

use Coroq\FlowFunction;

class FlowFunctionTest extends PHPUnit_Framework_TestCase {
  public function testMakeFlowFunctionFromEmptyFunction() {
    $flowFunction = FlowFunction::make("emptyFunction");
    $arguments = ["x" => 1];
    $result = $flowFunction($arguments);
    $this->assertSame($arguments, $result);
  }

  public function testMakeFlowFunctionFromEmptyClosure() {
    $flowFunction = FlowFunction::make(function() {});
    $arguments = ["x" => 1];
    $result = $flowFunction($arguments);
    $this->assertSame($arguments, $result);
  }

  public function testMakeFlowFunctionFromEmptyMethod() {
    $object = new EmptyClass();
    $flowFunction = FlowFunction::make([$object, "emptyMethod"]);
    $arguments = ["x" => 1];
    $result = $flowFunction($arguments);
    $this->assertSame($arguments, $result);
  }

  public function testMakeFlowFunctionFromEmptyStaticMethod() {
    $object = new EmptyClass();
    $flowFunction = FlowFunction::make([$object, "emptyStaticMethod"]);
    $arguments = ["x" => 1];
    $result = $flowFunction($arguments);
    $this->assertSame($arguments, $result);

    $flowFunction = FlowFunction::make(["EmptyClass", "emptyStaticMethod"]);
    $result = $flowFunction($arguments);
    $this->assertSame($arguments, $result);

    $flowFunction = FlowFunction::make("EmptyClass::emptyStaticMethod");
    $result = $flowFunction($arguments);
    $this->assertSame($arguments, $result);
  }

  public function testMakeFlowFunctionFromEmptyCallableObject() {
    $object = new EmptyCallableClass();
    $flowFunction = FlowFunction::make($object);
    $arguments = ["x" => 1];
    $result = $flowFunction($arguments);
    $this->assertSame($arguments, $result);
  }

  public function testMakeFlowFunctionFromEmptyFlowFunction() {
    $flowFunction = FlowFunction::make(FlowFunction::make(function() {
    }));
    $arguments = ["x" => 1];
    $result = $flowFunction($arguments);
    $this->assertSame($arguments, $result);
  }

  public function testFlowFunctionArgumentsBinding() {
    $test = $this;
    $flowFunction = FlowFunction::make(function($x, $y, $arguments) use ($test) {
      $test->assertSame(1, $x);
      $test->assertSame(2, $y);
      $test->assertSame(["x" => 1, "y" => 2], $arguments);
    });
    $result = $flowFunction(["x" => 1, "y" => 2]);
  }

  public function testFlowFunctionNullArgumentsBinding() {
    $test = $this;
    $flowFunction = FlowFunction::make(function($x, $y, $arguments) use ($test) {
      $test->assertSame(null, $x);
      $test->assertSame(null, $y);
      $test->assertSame([], $arguments);
    });
    $result = $flowFunction([]);
  }

  public function testFlowFunctionArgumentsBindingWithNestedFlowFunction() {
    $test = $this;
    $flowFunction = FlowFunction::make(FlowFunction::make(function($x, $y, $arguments) use ($test) {
      $test->assertSame(1, $x);
      $test->assertSame(2, $y);
      $test->assertSame(["x" => 1, "y" => 2], $arguments);
    }));
    $result = $flowFunction(["x" => 1, "y" => 2]);
  }

  public function testAssignmentInFlowFunction() {
    $flowFunction = FlowFunction::make(function($x) {
      return ["x" => 1];
    });
    $result = $flowFunction([]);
    $this->assertSame(["x" => 1], $result);
  }

  public function testOverwriteInFlowFunction() {
    $flowFunction = FlowFunction::make(function($x) {
      return ["x" => 2];
    });
    $result = $flowFunction(["x" => 1]);
    $this->assertSame(["x" => 2], $result);
  }

  public function testOverwriteWithNullInFlowFunction() {
    $flowFunction = FlowFunction::make(function($x) {
      return ["x" => null];
    });
    $result = $flowFunction(["x" => 1]);
    $this->assertSame(["x" => null], $result);
  }

  public function testAdditionInFlowFunction() {
    $flowFunction = FlowFunction::make(function($x, $y) {
      return ["x" => 1];
    });
    $result = $flowFunction(["y" => 2]);
    $this->assertSame(["x" => 1, "y" => 2], $result);
  }

  public function testFlowFunctionThrowsExceptionWhenStringReturned() {
    try {
      $flowFunction = FlowFunction::make(function() {
        return "test";
      });
      $flowFunction();
    }
    catch (\DomainException $e) {
      $this->assertRegExp("#^.+ defined in .+\\([0-9]+\\)#", $e->getMessage());
    }
  }

  public function testFlowFunctionCanCallPrivateMethod() {
    $object = new HasPrivateMethod();
    $result = $object->callPrivateMethod(["x" => "_"]);
    $this->assertSame(["x" => "_privateMethod"], $result);
  }

  public function testFlowFunctionCanCallPrivateStaticMethod() {
    $object = new HasPrivateMethod();
    $result = $object->callPrivateStaticMethod(["x" => "_"]);
    $this->assertSame(["x" => "_privateStaticMethod"], $result);
  }

  public function testFlowFunctionCanCallProtectedMethod() {
    $object = new InheritedProtectedMethod();
    $result = $object->callProtectedMethod(["x" => "_"]);
    $this->assertSame(["x" => "_protectedMethod"], $result);
  }

  public function testFlowFunctionCanCallProtectedStaticMethod() {
    $object = new InheritedProtectedMethod();
    $result = $object->callProtectedStaticMethod(["x" => "_"]);
    $this->assertSame(["x" => "_protectedStaticMethod"], $result);
  }
}
