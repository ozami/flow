<?php
namespace Coroq;

class Flow {
  /** @var array */
  public $funcs = [];

  /**
   * @param callable $func
   * @return self
   */
  public function to($func) {
    $this->funcs[] = $func;
    return $this;
  }

  /**
   * @param array $params
   * @return array
   */
  public function __invoke(array $params) {
    foreach ($this->funcs as $func) {
      $params = static::call($func, $params);
    }
    return $params;
  }

  /**
   * @param array $params
   * @return array
   */
  public function run(array $params) {
    return $this->__invoke($params);
  }

  /**
   * @param callable $func
   * @param array $params
   * @param callable|null $next
   * @return array
   */
  public static function call($func, array $params, $next = null) {
    if (!is_callable($func)) {
      throw new \InvalidArgumentException("The flow function is not callable");
    }
    $result = call_user_func($func, $params, $next);
    if ($result instanceof \Closure) {
      $result = $result($params);
    }
    if (!is_array($result) && !is_null($result)) {
      throw new \DomainException(
        "The flow function must return array or null. "
        . gettype($result) . " returned."
      );
    }
    return (array)$result + $params;
  }

  /**
   * @param array $a
   * @param string $path
   * @return mixed
   */
  public static function digArray(array $a, $path) {
    $path = explode("/", $path);
    foreach ($path as $node) {
      $a = @$a[$node];
    }
    return $a;
  }
}
