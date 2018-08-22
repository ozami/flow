<?php
namespace Coroq\Flow;
use \Coroq\Flow;

class MapToFile {
  /** @var string */
  public $which_param;

  /** @var string */
  public $dir;

  /**
   * @param string $which_param
   * @param string $dir
   */
  public function __construct($which_param, $dir) {
    $this->which_param = $which_param;
    $this->dir = rtrim($dir, "/");
    if (!is_dir($this->dir)) {
      throw new \LogicException("The map root directory '$this->dir' does not exist");
    }
  }

  /**
   * @param array $params
   * @return array
   */
  public function __invoke(array $params) {
    $path = Flow::digArray($params, $this->which_param);
    $path = $this->resolveDots($path);
    // force extension to .php
    $path = preg_replace("#[.][^.]+$#", "", $path) . ".php";
    // ignore direct access to __dir__.php and __all__.php
    $basename = basename($path);
    if ($basename == "__dir__.php" || $basename == "__all__.php") {
      return $params;
    }
    // build hook function tree
    $func = function() {};
    foreach ($this->loadHooksInPath($path) as $hook) {
      $func = function(array $params) use ($func, $hook) {
        return Flow::call($hook, $params, $func);
      };
    }
    return Flow::call($func, $params);
  }
  
  /**
   * @param string $path
   * @return array
   */
  public function loadHooksInPath($path) {
    $current = $this->dir;
    $hooks = [];
    foreach (explode("/", $path) as $i) {
      $hooks[] = "$current/__all__.php";
      $current .= "/$i";
    }
    $hooks[] = dirname("$this->dir/$path") . "/__dir__.php";
    $hooks[] = "$this->dir/$path";
    $hooks = array_reverse($hooks);
    $hooks = array_filter($hooks, "is_file");
    $hooks = array_map([$this, "loadFunctionFromFile"], $hooks);
    return $hooks;
  }

  /**
   * @param string $path
   * @return callable
   */
  public function loadFunctionFromFile($path) {
    $func = include $path;
    if (!is_callable($func)) {
      throw new \LogicException("Function from $path is not callable");
    }
    return $func;
  }
  
  /**
   * Resolve dot and dot-dot and slash-slash in path
   * @param string $path
   * @return string
   */
  public function resolveDots($path) {
    $path = explode("/", $path);
    $resolved = [];
    foreach ($path as $i) {
      if ($i == "." || $i == "") {
        continue;
      }
      if ($i == "..") {
        array_pop($resolved);
        continue;
      }
      $resolved[] = $i;
    }
    return join("/", $resolved);
  }
}
