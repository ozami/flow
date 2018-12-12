<?php

return function(array $args, callable $next) {
  $args["out"][] = "file";
  return $next($params);
};
