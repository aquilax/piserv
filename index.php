<?php

require 'piserv.php';

$routes = array(
  '#/(\d+)x(\d+)/(.+)#' => array(  // handles urls like "/120x10/3012.jpg"
    'action' => 'resize',
    'width' => '$1',
    'height' => '$2'
  ),
);

$is = new Piserv_Image($routes, '_i/');
$is->process();

?>
