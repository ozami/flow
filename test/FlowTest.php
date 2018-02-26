<?php
use Coroq\Flow;

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
}
