<?php

class Piserv_Base{

  private $_query = array();
  protected $config = array();
  protected $routes = array();

  public function __construct($routes, $query = FALSE){
    if (!$get){
      $this->_query = $_SERVER["QUERY_STRING"];
    }
    $this->routes = $routes;
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

  protected function error($code, $message){
    //TODO: Handle errors;
    echo $code . ' ' . $message;
  }

  protected function not_found(){
    $this->error(404, 'File not found');
  }

  protected function default_action(){
    $this->error(500, '');
  }

  private function route(){
    foreach ($this->routes as $match => $config){
      //match route
      if (preg_match($match, $this->_query, $matches)){
        if (count($matches) > 1){
          $conf = $this->processParams($config, $matches);
          $this->config = $conf;
          return TRUE;
        }
      }
    }
    return FALSE;
  }

  public function process(){
    $found = $this->route();
    if ($found){
      if (isset($this->config['action'])){
        $action = $this->config['action'];
        if (method_exists($this, $action)){
          $this->$action($this->config);
        } else {
          $this->error(500, 'Method not defined');
        }
      } else {
        $this->default_action();
      }
    } else {
      $this->not_found();
    }
  }

}

class Piserv_Image extends Piserv_Base{

  protected function resize($config){
    print_r($config);
  }

}


?>
