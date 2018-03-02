<?php

return function(array $params, $next) {
  $params["out"][] = "root-before";
  $params = Coroq\Flow::call($next, $params);
  $params["out"][] = "root-after";
  return $params;
};
