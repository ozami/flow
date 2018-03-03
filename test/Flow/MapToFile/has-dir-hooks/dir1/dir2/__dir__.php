<?php

return function(array $params, $next, $direct) {
  $params["out"][] = "dir2-before($direct)";
  $params = Coroq\Flow::call($next, $params);
  $params["out"][] = "dir2-after";
  return $params;
};
