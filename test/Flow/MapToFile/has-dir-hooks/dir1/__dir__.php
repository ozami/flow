<?php

return function(array $params, $next, $direct) {
  $params["out"][] = "dir1-before($direct)";
  $params = Coroq\Flow::call($next, $params);
  $params["out"][] = "dir1-after";
  return $params;
};
