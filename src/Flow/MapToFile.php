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
    // ignore direct access to __dir__.php
    if (basename($path) == "__dir__.php") {
      return $params;
    }
    // do nothing if the file specified with $path does not exist
    if (!is_file("$this->dir/$path")) {
      return $params;
    }
    // load directory hooks
    $current = $this->dir;
    $dir_hooks = [];
    foreach (explode("/", $path) as $i) {
      $dir_hooks[] = $this->loadDirectoryHook($current);
      $current .= "/$i";
    }
    $dir_hooks = array_reverse($dir_hooks);
    // load a function from the file
    $func = include "$this->dir/$path";
    if (!is_callable($func)) {
      throw new \LogicException();
    }
    // wrap file function with directory hooks
    foreach ($dir_hooks as $key => $dir_hook) {
      $direct = $key == 0;
      $func = function(array $params) use ($func, $dir_hook, $direct) {
        return (array)call_user_func($dir_hook, $params, $func, $direct) + $params;
      };
    }
    return Flow::call($func, $params);
  }
  
  /**
   * @param string $dir
   * @return callable
   */
  public function loadDirectoryHook($dir) {
    $dir_hook_file = "$dir/__dir__.php";
    if (!is_file($dir_hook_file)) {
      return function(array $params, $next) {
        return Flow::call($next, $params);
      };
    }
    $dir_hook = include $dir_hook_file;
    if (!is_callable($dir_hook)) {
      throw new \LogicException();
    }
    return $dir_hook;
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
