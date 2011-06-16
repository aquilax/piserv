<?php

require 'piserv.php';

$routes = array(
  '#/(\d+)x(\d+)/(.+)#' => array(
    'op' => 'resize',
    'w' => '$1',
    'h' => '$2'
  ),
);

$is = new Piserv_Base($routes);
$is->process();

?>
