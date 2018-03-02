<?php

return function(array $params, $next) {
  $params["out"][] = "root-before";
  $params = Coroq\Flow::invoke($next, $params);
  $params["out"][] = "root-after";
  return $params;
};
