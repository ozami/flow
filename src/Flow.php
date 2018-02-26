<?php
namespace Coroq;

class Flow {
  /** @var array */
  public $funcs = [];

  /**
   * @param callable $func
   * @return self
   */
  public function to($func) {
    $this->funcs[] = $func;
    return $this;
  }
  
  /**
   * @param array $params
   * @return array|null|void
   */
  public function __invoke(array $params) {
    foreach ($this->funcs as $func) {
      $params = static::invoke($func, $params);
    }
    return $params;
  }
  
  /**
   * @param array $params
   * @return array|null|void
   */
  public function run(array $params) {
    return $this->__invoke($params);
  }
  
  /**
   * @param array $param_defs
   * @param callable $func
   * @return callable
   */
  public static function nameParams(array $param_defs, $func) {
    return function(array $params) use ($param_defs, $func) {
      $named_params = [];
      foreach ($param_defs as $def) {
        $named_params[] = static::digArray($params, $def);
      }
      $named_params[] = $params;
      return call_user_func_array($func, $named_params);
    };
  }
  
  /**
   * @param string $which_param
   * @param string $rx
   * @param callable $func
   * @return callable
   */
  public static function ifMatches($which_param, $rx, $func) {
    return function(array $params) use ($which_param, $rx, $func) {
      $param = static::digArray($params, $which_param);
      if (preg_match($rx, $param)) {
        $params = static::invoke($func, $params);
      }
      return $params;
    };
  }
  
  /**
   * @param string $which_param
   * @param string $dir
   * @return callable
   */
  public static function mapToFile($which_param, $dir) {
    $dir = rtrim($dir, "/");
    return function(array $params) use ($which_param, $dir) {
      $path = static::digArray($params, $which_param);
      $path = ltrim($path, "/");
      // dot-dot not allowed
      if (strstr("/$path", "/../") !== false) {
        throw new \LogicException();
      }
      $path = "$dir/$path";
      // force extension to .php
      $path = preg_replace("#[.][^.]+$#", "", $path) . ".php";
      if (!is_file($path)) {
        return $params;
      }
      $func = include $path;
      return static::invoke($func, $params);
    };
  }
  
  /**
   * @param callable $func
   * @param array $params
   * @return array
   */
  public static function invoke($func, array $params) {
    if (!is_callable($func)) {
      throw new \LogicException();
    }
    $result = call_user_func($func, $params);
    if (!is_array($result) && !is_null($result)) {
      throw new \LogicException();
    }
    return (array)$result + $params;
  }
  
  /**
   * @param array $a
   * @param string $path
   * @return mixed
   */
  public static function digArray(array $a, $path) {
    $path = explode("/", $path);
    foreach ($path as $node) {
      $a = @$a[$node];
    }
    return $a;
  }
}
