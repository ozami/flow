<?php

return function(array $params, $next) {
  $params["out"][] = "dir1-before";
  $params = Coroq\Flow::invoke($next, $params);
  $params["out"][] = "dir1-after";
  return $params;
};
