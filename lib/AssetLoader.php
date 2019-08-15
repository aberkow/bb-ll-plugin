<?php

namespace BBLL\Lib;

class AssetLoader {
  public static function enqueue_scripts() {
    wp_enqueue_style('test-style', BB_LL_URL . 'build/css/main.css');
    wp_enqueue_script('test-script', BB_LL_URL . 'build/js/index.js');
  }
}