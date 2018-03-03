<?php

return function(array $params, $next, $direct) {
  $params["out"][] = "root-before($direct)";
  $params = Coroq\Flow::call($next, $params);
  $params["out"][] = "root-after";
  return $params;
};
