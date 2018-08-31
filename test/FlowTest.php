<?php
use Coroq\Flow;

function test_flow_function() {
  return ["test_flow_function" => true];
}

class FlowTest extends PHPUnit_Framework_TestCase {
  public function testEmptyFlowReturnsParamsAsPassed() {
    $flow = new Flow();
    $params = ["x" => 1];
    $result = $flow($params);
    $this->assertSame($params, $result);
  }

  public function testRunCallsAllFunctions() {
    $flow = new Flow();
    $result = $flow
      ->to(function($params) {
        $params["x"] += 1;
        return $params;
      })
      ->to(function($params) {
        $params["x"] += 1;
        return $params;
      })
      ->run(["x" => 0]);
    $this->assertEquals($result["x"], 2);
  }

  public function testNameParams() {
    $flow = new Flow();
    $params = [
      "x" => "x",
      "y" => ["yy" => "yy", "z" => "z"],
    ];
    $result = $flow
      ->to(Flow::nameParams(["x", "y", "y/z"], function($x, $y, $z, $all) {
        return compact("x", "y", "z", "all");
      }))
      ->run($params);
    $this->assertSame([
      "x" => $params["x"],
      "y" => $params["y"],
      "z" => $params["y"]["z"],
      "all" => $params,
    ], $result);
  }

  public function testFlowCanCallFunctionNameString() {
    $flow = new Flow();
    $result = $flow
      ->to("test_flow_function")
      ->run([]);
    $this->assertSame([
      "test_flow_function" => true,
    ], $result);
  }

  /**
   * @expectedException InvalidArgumentException
   */
  public function testFlowThrowsExceptionWhenFlowFunctionWasNull() {
    $flow = new Flow();
    $flow
      ->to(null)
      ->run([]);
  }

  /**
   * @expectedException InvalidArgumentException
   */
  public function testFlowThrowsExceptionWhenFlowFunctionWasNotCallableString() {
    $flow = new Flow();
    $flow
      ->to("non_existing_function")
      ->run([]);
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

  /**
   * @expectedException DomainException
   */
  public function testFlowThrowsExceptionWhenFlowFunctionReturnsClosureThatReturnsString() {
    $flow = new Flow();
    $flow
      ->to(function($params) {
        return function($params) {
          return "test";
        };
      })
      ->run([]);
  }

  /**
   * @expectedException DomainException
   */
  public function testFlowThrowsExceptionWhenFlowFunctionReturnsClosureThatReturnsObject() {
    $flow = new Flow();
    $flow
      ->to(function($params) {
        return function($params) {
          return new \stdClass();
        };
      })
      ->run([]);
  }
}
