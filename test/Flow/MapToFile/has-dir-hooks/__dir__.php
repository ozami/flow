<?php

return function(array $params, $next) {
  $params["out"][] = "root-dir-begin";
  $params = $next($params);
  $params["out"][] = "root-dir-end";
  return $params;
};
