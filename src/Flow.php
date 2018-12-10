<?php
namespace Coroq;

class Flow {
  /** @var array */
  private $funcs = [];

  /**
   * @param callable $func
   * @return self
   */
  public function to(callable $func) {
    $this->funcs[] = $func;
    return $this;
  }

  /**
   * @param array $args
   * @param callable|null $next
   * @return array
   */
  public function __invoke(array $args = [], callable $next = null) {
    $funcs = $this->funcs;
    if ($next) {
      $funcs[] = $next;
    }
    $call = function(array $args) use (&$call, &$funcs) {
      if (!$funcs) {
        return $args;
      }
      $func = array_shift($funcs);
      $result = call_user_func($func, $args, $call);
      if (!is_array($result)) {
        $type = gettype($result);
        throw new \DomainException(
          "The flow function must return an array. ($type returned)"
        );
      }
      return $result;
    };
    return $call($args);
  }

  /**
   * @param array $args
   * @param callable|null $next
   * @return array
   */
  public function run(array $args = [], callable $next = null) {
    return $this->__invoke($args, $next);
  }

  /**
   * @param callable $func
   * @return self
   */
  public function toV3(callable $func) {
    return $this->to(static::v3($func));
  }

  /**
   * @param callable $func
   * @return \Closure
   */
  public static function v3(callable $func) {
    return function(array $args, callable $next) use ($func) {
      return $next(static::call($func, $args, $next));
    };
  }

  /**
   * For version 3 compatibility
   * @param callable $func
   * @param array $args
   * @param callable|null $next
   * @return array
   */
  public static function call($func, array $args, $next = null) {
    $result = call_user_func($func, $args, $next);
    if (!is_array($result) && is_callable($result)) {
      $result = $result($args);
    }
    if (!is_array($result) && !is_null($result)) {
      $type = gettype($result);
      throw new \DomainException(
        "The v3 flow function must return an array or null. ($type returned)"
      );
    }
    return (array)$result + $args;
  }
}
