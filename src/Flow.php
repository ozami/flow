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
      while (true) {
        $result = call_user_func($func, $args, $call);
        if (!is_callable($result)) {
          break;
        }
        $func = $result;
      }
      if (!is_array($result)) {
        $ref = $this->reflectionCallable($func);
        throw new \DomainException(sprintf(
          "%s defined in %s(%s) returned %s. (Flow function must return an array)",
          $ref->getName(),
          $ref->getFileName(),
          $ref->getStartLine(),
          getType($result)
        ));
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
   * @param callable $callable
   * @return \ReflectionFunctionAbstract
   */
  private function reflectionCallable($callable) {
    if (is_array($callable)) {
      return new \ReflectionMethod($callable[0], $callable[1]);
    }
    if ($callable instanceof \Closure) {
      return new \ReflectionFunction($callable);
    }
    if (is_object($callable)) {
      return new \ReflectionMethod($callable, "__invoke");
    }
    return new \ReflectionFunction($callable);
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
