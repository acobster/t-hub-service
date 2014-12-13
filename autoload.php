<?php

spl_autoload_register( function($class) {
  $class = str_replace('\\', '/', $class);
  $file = "{$class}.php";

  if( defined('TWEB_APP_ROOT') ) {
    $file = TWEB_APP_ROOT . $file;
  }

  if( file_exists($file) ) {
    include $file;
  }
});