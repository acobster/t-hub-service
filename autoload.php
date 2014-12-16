<?php

spl_autoload_register( function($class) {
  $class = str_replace('\\', '/', $class);
  $file = "{$class}.php";

  if( ! defined('TWEB_LIB_ROOT') ) {
    define( 'TWEB_LIB_ROOT', __DIR__.'/lib/');
  }

  $file = TWEB_LIB_ROOT . $file;

  if( file_exists($file) ) {
    include $file;
  }
});
