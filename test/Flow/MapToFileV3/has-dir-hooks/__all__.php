<?php

return function(array $params, $next) {
  $params["out"][] = "root-all-begin";
  $params = Coroq\Flow::call($next, $params);
  $params["out"][] = "root-all-end";
  return $params;
};
