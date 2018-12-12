<?php
use Coroq\Flow;

function test_flow_function($args, $next) {
  return $next(["test_flow_function" => true] + $args);
}

class PushToArray {
  public function __construct($value) {
    $this->value = $value;
  }
  public function run($args, $next) {
    $args["x"][] = $this->value;
    return $next($args);
  }
}

function pushToArray($value) {
  return function($args, $next) use ($value) {
    $args["x"][] = $value;
    return $next($args);
  };
}

function returnString() {
  return "test";
}

class ReturnString {
  public function __invoke() {
    return "test";
  }
  public function run() {
    return "test";
  }
}

class FlowTest extends PHPUnit_Framework_TestCase {
  public function testEmptyFlowReturnsParamsAsPassed() {
    $flow = new Flow();
    $params = ["x" => 1];
    $result = $flow($params);
    $this->assertSame($params, $result);
  }

  public function testFlowCallsAllFunctionsInOrder() {
    $result = (new Flow())
      ->to(pushToArray(0))
      ->to(pushToArray(1))
      ->run(["x" => []]);
    $this->assertEquals(["x" => [0, 1]], $result);
  }

  public function testFlowCanCallFunctionNameString() {
    $result = (new Flow())
      ->to("test_flow_function")
      ->run();
    $this->assertSame([
      "test_flow_function" => true,
    ], $result);
  }

  public function testArrayCallable() {
    $result = (new Flow())
      ->to(pushToArray(0))
      ->to([new PushToArray(1), "run"])
      ->to(pushToArray(2))
      ->run();
    $this->assertSame(["x" => [0, 1, 2]], $result);
  }

  public function testFlowToFlow() {
    $sub_flow = (new Flow())
      ->to(pushToArray(1))
      ->to(pushToArray(2));
    $result = (new Flow())
      ->to(pushToArray(0))
      ->to($sub_flow)
      ->to(pushToArray(3))
      ->run(["x" => []]);
    $this->assertSame(["x" => [0, 1, 2, 3]], $result);
  }

  public function testEarlyReturn() {
    $result = (new Flow())
      ->to(pushToArray(0))
      ->to(function($args) {
        return $args;
      })
      ->to(pushToArray(1))
      ->run(["x" => []]);
    $this->assertSame(["x" => [0]], $result);
  }

  /**
   * @expectedException DomainException
   */
  public function testFlowThrowsExceptionWhenFlowFunctionReturnsString() {
    $flow = new Flow();
    $flow
      ->to(function($params) {
        return "test";
      })
      ->run([]);
  }

  public function testFlowThrowsExceptionWithFunctionDetail() {
    $rx = "#^.+ defined in .+\\([0-9]+\\)#";
    // closure
    try {
      (new Flow())
        ->to(function($params) {
          return "test";
        })
        ->run();
    }
    catch (\DomainException $e) {
      $this->assertRegExp($rx, $e->getMessage());
    }
    // function name string
    try {
      (new Flow())
        ->to("returnString")
        ->run();
    }
    catch (\DomainException $e) {
      $this->assertRegExp($rx, $e->getMessage());
    }
    // __invoke()able object
    try {
      (new Flow())
        ->to(new ReturnString())
        ->run();
    }
    catch (\DomainException $e) {
      $this->assertRegExp($rx, $e->getMessage());
    }
    // array
    try {
      (new Flow())
        ->to([new ReturnString(), "run"])
        ->run();
    }
    catch (\DomainException $e) {
      $this->assertRegExp($rx, $e->getMessage());
    }
  }

  /**
   * @expectedException DomainException
   */
  public function testFlowThrowsExceptionWhenFlowFunctionReturnsObject() {
    $flow = new Flow();
    $flow
      ->to(function($params) {
        return new \stdClass();
      })
      ->run([]);
  }

  public function testV3() {
    $result = (new Flow())
      ->to(Flow::v3(function($args) {
        return ["abc" => "abc"];
      }))
      ->to(Flow::v3(function($args) {
        return ["def" => "def"];
      }))
      ->to(Flow::v3(function($args) {
        return;
      }))
      ->run();
    $this->assertEquals($result, [
      "abc" => "abc",
      "def" => "def",
    ]);
  }

  public function testV3OnTheFlowFunctionReturnsFunction() {
    $result = (new Flow())
      ->to(pushToArray(0))
      ->to(Flow::v3(function() {
        return function($args) {
          $x = $args["x"];
          $x[] = 1;
          return compact("x");
        };
      }))
      ->to(pushToArray(2))
      ->run();
    $this->assertSame(["x" => [0, 1, 2]], $result);
  }

  /**
   * @expectedException DomainException
   */
  public function testV3ThrowsExceptionWhenFlowFunctionReturnsString() {
    $result = (new Flow())
      ->to(Flow::v3(function($args) {
        return "test";
      }))
      ->run();
  }

  public function testToV3() {
    $result = (new Flow())
      ->toV3(function($args) {
        return ["abc" => "abc"];
      })
      ->toV3(function($args) {
        return ["def" => "def"];
      })
      ->toV3(function($args) {
        return;
      })
      ->run();
    $this->assertEquals($result, [
      "abc" => "abc",
      "def" => "def",
    ]);
  }
}
