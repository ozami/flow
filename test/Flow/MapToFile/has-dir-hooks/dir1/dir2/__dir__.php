<?php

return function(array $params, $next) {
  $params["out"][] = "dir2-before";
  $params = Coroq\Flow::invoke($next, $params);
  $params["out"][] = "dir2-after";
  return $params;
};
