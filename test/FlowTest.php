<?php
require_once __DIR__ . "/sample.php";

use Coroq\Flow;

/**
 * @covers Coroq\Flow
 */
class FlowTest extends PHPUnit_Framework_TestCase {
  public function testEmptyFlowReturnsArgumentsAsPassed() {
    $flow = new Flow();
    $arguments = ["x" => 1];
    $result = $flow($arguments);
    $this->assertSame($arguments, $result);
  }

  public function testFlowCallsAllFunctionsInOrder() {
    $result = (new Flow())
      ->to(makePushToArray(1))
      ->to(makePushToArray(2))
      ->run(["x" => []]);
    $this->assertEquals(["x" => [1, 2]], $result);
  }

  public function testCallingFunctionNameString() {
    $result = (new Flow())
      ->to("setXTo1")
      ->run();
    $this->assertSame(["x" => 1], $result);
  }

  public function testCallingFunctionArrayNotation() {
    $result = (new Flow())
      ->to([new PushToArray(1), "run"])
      ->run();
    $this->assertSame(["x" => [1]], $result);
  }

  public function testFlowCanCallFlow() {
    $sub_flow = (new Flow())
      ->to(makePushToArray(1))
      ->to(makePushToArray(2));
    $result = (new Flow())
      ->to(makePushToArray(0))
      ->to($sub_flow)
      ->to(makePushToArray(3))
      ->run(["x" => []]);
    $this->assertSame(["x" => [0, 1, 2, 3]], $result);
  }
}
