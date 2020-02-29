<?php
namespace Coroq;

interface FlowFunctionInterface {
  /**
   * Execute flow function
   * @param array $arguments
   * @return array result of flow function
   */
  public function __invoke(array $arguments = []);
}
