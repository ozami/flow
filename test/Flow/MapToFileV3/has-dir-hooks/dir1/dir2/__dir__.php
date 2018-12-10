<?php

return function(array $params, $next) {
  $params["out"][] = "dir2-dir-begin";
  $params = Coroq\Flow::call($next, $params);
  $params["out"][] = "dir2-dir-end";
  return $params;
};
