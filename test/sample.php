<?php

function emptyFunction() {
}

class EmptyClass {
  public function emptyMethod() {
  }
  public static function emptyStaticMethod() {
  }
}

class EmptyCallableClass {
  public function __invoke() {
  }
}

function setXTo1() {
  return ["x" => 1];
}

class PushToArray {
  public function __construct($value) {
    $this->value = $value;
  }
  public function run($x) {
    $x[] = $this->value;
    return compact("x");
  }
}

function makePushToArray($value) {
  return function($x) use ($value) {
    $x[] = $value;
    return compact("x");
  };
}
