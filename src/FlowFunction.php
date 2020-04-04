<?php
namespace Coroq;

class FlowFunction {
  /**
   * Make a flow function from a normal function
   * @param callable $function
   * @return \Closure flow function
   */
  public static function make($function) {
    return makeFlowFunction($function);
  }

  /**
   * Make a flow function from normal function and call it
   * @param callable $function
   * @param array $arguments
   * @return array return value of the flow function
   */
  public static function call($function, array $arguments = []) {
    $flow = static::make($function);
    return $flow($arguments);
  }

  /**
   * @param callable $callable
   * @return \ReflectionFunctionAbstract
   */
  public static function reflectionCallable($callable) {
    if (is_array($callable)) {
      return new \ReflectionMethod($callable[0], $callable[1]);
    }
    if ($callable instanceof \Closure) {
      return new \ReflectionFunction($callable);
    }
    if (is_object($callable)) {
      return new \ReflectionMethod($callable, "__invoke");
    }
    if (is_string($callable)) {
      if (strpos($callable, "::") === false) {
        return new \ReflectionFunction($callable);
      }
      return new \ReflectionMethod($callable);
    }
    // @codeCoverageIgnoreStart
    throw new \LogicException("Unknown type of callable. " . gettype($callable));
    // @codeCoverageIgnoreEnd
  }
}

/**
 * Workaround for PHP 5
 * Use FlowFunction::make() instead.
 * @param callable $function
 * @return \Closure flow function
 */
function makeFlowFunction($function) {
  $reflection = FlowFunction::reflectionCallable($function);
  return function(array $arguments = []) use ($function, $reflection) {
    $named_arguments = [];
    foreach ($reflection->getParameters() as $parameter) {
      $parameter_name = $parameter->getName();
      if ($parameter_name == "arguments") {
        $named_arguments[] = $arguments;
      }
      else {
        $named_arguments[] = @$arguments[$parameter_name];
      }
    }
    $result = call_user_func_array($function, $named_arguments);
    if (!is_array($result) && !is_null($result)) {
      throw new \DomainException(sprintf(
        "%s defined in %s(%s) returned %s. (Flow function must return an array or null)",
        $reflection->getName(),
        $reflection->getFileName(),
        $reflection->getStartLine(),
        gettype($result)
      ));
    }
    return (array)$result + $arguments;
  };
}
