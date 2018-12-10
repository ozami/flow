<?php
namespace Coroq\Flow;
use \Coroq\Flow;

class MapToFileV3 extends MapToFile {
  /**
   * @param string $path
   * @return callable
   */
  public function loadFunctionFromFile($path) {
    return Flow::v3(parent::loadFunctionFromFile($path));
  }
}
