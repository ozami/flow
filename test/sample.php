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

class HasPrivateMethod {
  private function privateMethod($x) {
    $x .= "privateMethod";
    return compact("x");
  }
  private static function privateStaticMethod($x) {
    $x .= "privateStaticMethod";
    return compact("x");
  }
  public function callPrivateMethod(array $arguments) {
    $function = \Coroq\FlowFunction::make([$this, "privateMethod"])->bindTo($this, $this);
    return $function($arguments);
  }
  public static function callPrivateStaticMethod(array $arguments) {
    $function = \Coroq\FlowFunction::make(["HasPrivateMethod", "privateStaticMethod"])->bindTo(null, "HasPrivateMethod");
    return $function($arguments);
  }
}

class HasProtectedMethod {
  protected function protectedMethod($x) {
    $x .= "protectedMethod";
    return compact("x");
  }
  protected function protectedStaticMethod($x) {
    $x .= "protectedStaticMethod";
    return compact("x");
  }
}

class InheritedProtectedMethod extends HasProtectedMethod {
  public function callProtectedMethod(array $arguments) {
    $function = \Coroq\FlowFunction::make([$this, "protectedMethod"])->bindTo($this, $this);
    return $function($arguments);
  }
  public static function callProtectedStaticMethod(array $arguments) {
    $function = \Coroq\FlowFunction::make(["InheritedProtectedMethod", "protectedStaticMethod"])->bindTo(null, "InheritedProtectedMethod");
    return $function($arguments);
  }
}
