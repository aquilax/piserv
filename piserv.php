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

  private function validate(){
    //TODO More validation is needed here
    $q = $this->_query;
    $this->_query = str_replace(array('..', ':', '<', '>'), '', $q);
    return TRUE;
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
    $valid = $this->validate();
    if ($valid){
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
    } else {
      $this->not_found();
    }
  }

}

class Piserv_Image extends Piserv_Base{

  protected $imagesize = null;

  protected function getImageType(){
    $is = getimagesize($this->source);
    if ($is){
      $this->imagesize = $is;
      return $is['mime'];
    }
    $this->error(404, 'Image not found');
  }

  protected function getNewSize(){
    $dw = $this->config['w']; // desired width
    $dh = $this->config['h']; //desired height
    $cw = $this->imagesize[0]; //current width
    $ch = $this->imagesize[1]; //current height
    $dw = ($dw > $cw)?$cw:$dw; // don't make the image wider
    $dh = ($dh > $ch)?$ch:$dh; // don't make the image higher

    $x_ratio = ($dw / $cw);
    $y_ratio = ($dh / $ch);

    //Calculate the new size
    if($x_ratio * $ch < $dh){
      return array($dw, ceil($x_ratio * $ch), $cw, $ch);
    } else {
      return array(ceil($y_ratio * $cw), $dw, $cw, $ch);
    }
  }

  protected function processImage($function_name){
    $isource = $function_name($this->source);
    list($new_w, $new_h, $old_w, $old_h) = $this->getNewSize();
    $idestination = imagecreatetruecolor($new_w, $new_h);
    imagecopyresampled($idestination, $isource, 0, 0, 0, 0, $new_w, $new_h, $old_w, $old_h);
    unset($isource);
    return $idestination;
  }

  protected function save($image, $output_function){
    return TRUE;
    return $output_function($image, $this->destination.'/'.$this->file_name);
  }

  protected function stream($image, $output_function, $image_type){
    header(sprintf('Content-Type: %s', $image_type));
    $output_function($image);
  }

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
      $image_type = $this->getImageType();
      switch ($image_type){
        case 'image/png' : $process_function = 'imagecreatefrompng'; $output_function = 'imagepng'; break;
        case 'image/gif' : $process_function = 'imagecreatefromgif'; $output_function = 'imagegif'; break;
        case 'image/jpeg': $process_function = 'imagecreatefromjpeg'; $output_function = 'imagejpeg'; break;
        default: $this->error(500, 'Image not supported');
      }
      $image = $this->processImage($process_function);
      if ($this->save($image, $output_function)){
        $this->stream($image, $output_function, $image_type);
      } else {
        $this->error('500', 'Cannot create image');
      }
    }
  }

}


?>
