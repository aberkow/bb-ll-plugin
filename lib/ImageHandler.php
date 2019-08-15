<?php

namespace BBLL\Lib;

class ImageHandler {
  /**
   * Create custom image sizes from an array.
   *
   * @return void
   */
  public static function create_image_sizes() {
    $image_sizes = self::get_image_sizes();
    foreach ($image_sizes as $name => $size) {
      add_image_size($name, $size['width'], $size['height']);
    }
  }
  private static function get_image_sizes() {
    return array(
      'bb-lazy-load' => array(
          'readable_name' => __('Lazy Load', 'uconn-2019'),
          'width' => 50,
          'height' => 50
      ),
    );
  }
}