<?php

return function(array $params, $next) {
  $params["out"][] = "dir2-all-begin";
  $params = $next($params);
  $params["out"][] = "dir2-all-end";
  return $params;
};
