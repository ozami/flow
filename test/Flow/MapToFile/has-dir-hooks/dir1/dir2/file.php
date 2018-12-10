<?php

return function(array $params, callable $next) {
  $params["out"][] = "file";
  return $next($params);
};
