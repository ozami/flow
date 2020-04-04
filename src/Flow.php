<?php
namespace Coroq;

class Flow {
  /** @var array */
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
   * @return array result of all flow functions
   */
  public function __invoke(array $arguments = []) {
    foreach ($this->functions as $function) {
      $arguments = FlowFunction::call($function, $arguments);
    }
    return $arguments;
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
