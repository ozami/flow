<?php

return function(array $params, $next) {
  $params["out"][] = "dir1-dir-begin";
  $params = Coroq\Flow::call($next, $params);
  $params["out"][] = "dir1-dir-end";
  return $params;
};
