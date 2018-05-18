<?php

return function(array $params, $next) {
  $params["out"][] = "dir1-all-begin";
  $params = Coroq\Flow::call($next, $params);
  $params["out"][] = "dir1-all-end";
  return $params;
};
