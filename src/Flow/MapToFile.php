<?php
namespace Coroq\Flow;
use \Coroq\Flow;

class MapToFile {
  /** @var string */
  private $dir;

  /** @var callable */
  private $getPath;

  /**
   * @param string $dir
   * @param callable $getPath
   */
  public function __construct($dir, callable $getPath) {
    $this->dir = rtrim($dir, "/");
    if (!is_dir($this->dir)) {
      throw new \InvalidArgumentException(
        "The map root directory '$this->dir' does not exist"
      );
    }
    $this->getPath = $getPath;
  }

  /**
   * @param array $args
   * @param callable $next
   * @return array
   */
  public function __invoke(array $args, callable $next) {
    $path = call_user_func($this->getPath, $args);
    $path = $this->resolveDots($path);
    // change the extension to .php
    $path = preg_replace("#[.][^.]+$#", "", $path) . ".php";
    // ignore direct access to __dir__.php and __all__.php
    $basename = basename($path);
    if ($basename == "__dir__.php" || $basename == "__all__.php") {
      return $next($args);
    }
    // build hook flow
    $flow = new Flow();
    foreach ($this->loadHooksInPath($path) as $hook) {
      $flow->to($hook);
    }
    $flow->to($next);
    return $flow->run($args);
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
      throw new \DomainException("Function read from $path is not callable");
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
