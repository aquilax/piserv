<?php

require 'piserv.php';

$routes = array(
  '#/(\d+)x(\d+)/(.+)#' => array(  // handles urls like "/120x10/3012.jpg"
    'action' => 'resize',
    'w' => '$1',
    'h' => '$2'
  ),
);

$is = new Piserv_Image($routes, '_i/');
$is->process();

?>
