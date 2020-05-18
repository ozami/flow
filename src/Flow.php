<?php
namespace Coroq;

class Flow {
  /** @var array<callable> */
  private $functions;

  /**
   * Constructor
   */
  public function __construct() {
    $this->functions = [];
  }

  /**
   * Execute all child flow functions
   * @param array $arguments
   * @param ?callable $next
   * @return array result of all flow functions
   */
  public function __invoke(array $arguments = [], callable $next = null) {
    $functions = array_map('Coroq\FlowFunction::make', $this->functions);
    $function_chain = $this->chainFunctions($functions);
    $result = $function_chain($arguments);
    if ($next) {
      $result = $next($result);
    }
    return $result;
  }

  /**
   * @param array<callable> $functions
   * @return \Closure
   */
  private function chainFunctions(array $functions) {
    $functions = array_reverse($functions);
    $function_chain = function($arguments) {
      return $arguments;
    };
    foreach ($functions as $function) {
      $next = $function_chain;
      $function_chain = function($arguments) use ($function, $next) {
        return $function($arguments, $next);
      };
    }
    return $function_chain;
  }

  /**
   * Alias of __invoke()
   * @param array $arguments
   * @return array result of all flow functions
   */
  public function run(array $arguments = []) {
    return $this->__invoke($arguments);
  }

  /**
   * Add flow function
   * @param callable $function
   * @return self
   */
  public function to($function) {
    $this->functions[] = $function;
    return $this;
  }
}
