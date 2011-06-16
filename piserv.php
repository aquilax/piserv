<?php

class Piserv_Base{

  private $_query = array();
  protected $routes = array();

  function __construct($routes, $query = FALSE){
    if (!$get){
      $this->_query = $_SERVER["QUERY_STRING"];
    }
    $this->routes = $routes;
    print_r($this->_query);
  }

  private function processParams($config, $matches){
    foreach ($config as $name => $val){
      if (preg_match('/\$(\d+)/', $val, $mat)){
        $index = $mat[1];
        if (isset($matches[$index])){
          $config[$name] = $matches[$index];
        }
      }
    }
    return $config;
  }

  private function route(){
    foreach ($this->routes as $match => $config){
      //match route
      if (preg_match($match, $this->_query, $matches)){
        if (count($matches) > 1){
          $conf = $this->processParams($config, $matches);
          print_r($conf);
        }
        break;
      }
    }
  }

  public function process(){
    $this->route();
  }

}

?>
