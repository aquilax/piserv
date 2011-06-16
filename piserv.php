<?php

class Piserv_Base{

  protected $_query = array();
  protected $config = array();
  protected $routes = array();
  protected $images_base = '';

  public function __construct($routes, $images_base, $query = FALSE){
    if (!$get){
      $this->_query = $_SERVER["QUERY_STRING"];
    }
    $this->routes = $routes;
    $this->images_base = $images_base;
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
    die();
  }

  protected function not_found(){
    $this->error(404, 'File not found');
  }

  protected function default_action(){
    $this->error(500, '');
  }

  protected function forceDir($dest){
    if (file_exists($dest)){
      if (is_file($dest)){
        $this->error(500, 'Cannot create directory');
        return FALSE;
      } else {
        return TRUE;
      }
    } else {
      if (mkdir($dest, 0665, TRUE)){
        return TRUE;
      } else {
        $this->error(500, 'Cannot create directory');
        return FALSE;
      }
    }
    return FALSE;
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
    $this->source = realpath($this->images_base.basename($this->_query));
    if (!file_exists($this->source)){
      $this->not_found();
    }
    $this->file_name = basename($this->source);
    $this->base_path = pathinfo(__FILE__, PATHINFO_DIRNAME);
    $dest = $this->base_path.dirname($this->_query);
    if ($this->forceDir($dest)){
      $this->destination = realpath($dest);
    }
  }

}


?>
