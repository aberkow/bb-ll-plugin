<?php

namespace BBLL\Lib;

class AssetLoader {
  public static function enqueue_scripts() {
    // wp_enqueue_style('bb-ll', BB_LL_URL . 'build/css/main.css');
    wp_enqueue_script('bb-ll', BB_LL_URL . 'build/js/index.js', array(), null, true);
  }
}