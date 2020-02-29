<?php
namespace Coroq;

class FlowFunction implements FlowFunctionInterface {
  /** @var callable */
  private $function;

  /**
   * Constructor
   * @param callable|null $function normal function
   */
  public function __construct(callable $function = null) {
    $this->function = $function;
  }

  /**
   * Execute flow function
   * @param array $arguments
   * @return array result of the flow function
   */
  public function __invoke(array $arguments = []) {
    $function = $this->function;
    if ($function === null) {
      return $arguments;
    }
    if ($function instanceof FlowFunctionInterface) {
      $result = $function($arguments);
    }
    else {
      $reflection = static::reflectionCallable($function);
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
    }
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
  }

  /**
   * Make a flow function from a normal function
   * @param callable $function
   * @return FlowFunctionInterface flow function
   */
  public static function make(callable $function) {
    if ($function instanceof FlowFunctionInterface) {
      return $function;
    }
    return new FlowFunction($function);
  }

  /**
   * Call function as a flow function
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
  private static function reflectionCallable(callable $callable) {
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
