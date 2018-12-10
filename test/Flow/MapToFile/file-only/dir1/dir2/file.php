<?php

return function(array $params, callable $next) {
  return $next(["out" => "file"] + $params);
};
