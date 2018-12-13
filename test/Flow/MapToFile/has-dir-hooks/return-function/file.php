<?php

return function($args, $next) {
  $args["out"][] = "outer";
  return function($args, $next) {
    $args["out"][] = "inner";
    return $next($args);
  };
};
