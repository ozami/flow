<?php

return function(array $params, $next) {
  $params["out"][] = "root-all-begin";
  $params = $next($params);
  $params["out"][] = "root-all-end";
  return $params;
};
