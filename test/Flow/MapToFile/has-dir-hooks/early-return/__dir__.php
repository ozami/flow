<?php

return function($args, $next) {
  $args["out"][] = "early-return-dir";
  // early return
  return $args;
};
