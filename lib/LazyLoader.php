<?php

namespace BBLL\Lib;

class LazyLoader {
  /**
   * Add settings options to modules and forms to enable lazy loading images
   * - select box to enable/disable
   * - a color picker to set background color
   *
   * @param array $form
   * @param string $id
   * @return array
   */
  public static function filter_settings_form(array $form, string $id): array {

    $lazy_loader = array(
      'type' => 'select',
      'label' => __('Lazy Loaded Image(s)', 'uconn-2019'),
      'default' => 'false',
      'options' => array(
        'false' => __('Not Lazy Loaded', 'uconn-2019'),
        'true' => __('Please Lazy Load the Image', 'uconn-2019')
      )
    );

    $lazy_loader_bg = array(
      'type' => 'color',
      'label' => __('Lazy Loader Background Color', 'uconn-2019'),
      'default' => 'cccccc',
      'show_reset' => true
    );


    switch ($id) {
      case 'row':
        $form['tabs']['style']['sections']['bg_photo']['fields']['lazy_loader'] = $lazy_loader;
        $form['tabs']['style']['sections']['bg_photo']['fields']['lazy_loader_color'] = $lazy_loader_bg;
        break;
      case 'gallery':
        $form['general']['sections']['general']['fields']['lazy_loader'] = $lazy_loader;
        $form['general']['sections']['general']['fields']['lazy_loader_color'] = $lazy_loader_bg;
        break;
      case 'photo':
        $form['general']['sections']['general']['fields']['lazy_loader'] = $lazy_loader;
        $form['general']['sections']['general']['fields']['lazy_loader_color'] = $lazy_loader_bg;
        break;
      default:
        return $form;
        break;
    }



    // if ($id === 'row') {
    //     $form['tabs']['style']['sections']['bg_photo']['fields']['lazy_loader'] = $lazy_loader;
    //     $form['tabs']['style']['sections']['bg_photo']['fields']['lazy_loader_color'] = $lazy_loader_bg;
    // }

    // if ($id === 'photo') {
    //   $form['general']['sections']['general']['fields']['lazy_loader'] = $lazy_loader;
    //   $form['general']['sections']['general']['fields']['lazy_loader_color'] = $lazy_loader_bg;
    // }


    return $form;
  }

  /**
   * Attach additional html attributes to beaver builder rows/modules
   *
   * @param array $atts
   * @param object $el
   * @return array
   */
  public static function lazy_loader_atts(array $atts, object $el): array {
    if (isset($el->settings->lazy_loader) && $el->settings->lazy_loader === 'true') {
      $atts['data-lazy-loaded'] = "true";
      $atts['data-lazy-style'] = $el->settings->lazy_loader_color;
      $atts['data-img-src'] = $el->settings->bg_image_src ?? $el->settings->photo_src;
    }
    return $atts;
  }

  /**
   * Generate additional css to prevent row background images from being loaded.
   * A background-color is set and then a transparent overlay using linear-gradient.
   *
   * @param string $css
   * @param array $nodes
   * @param array $global_settings
   * @return string
   */
  public static function filter_row_css(string $css, array $nodes, object $global_settings): string {

    foreach ($nodes['rows'] as $id => $row) {

      
      
      if ($row->settings->lazy_loader === 'true') {
        $upload_dir = wp_upload_dir();
        $baseurl = $upload_dir['baseurl'];

        $img_url = $row->settings->bg_image_src;
        $img_id = self::get_id_from_url($img_url);
        $img_meta = wp_get_attachment_metadata($img_id);

        $lazy_img_path = substr_replace($img_meta['file'], $img_meta['sizes']['bb-lazy-load']['file'], 8);

        $lazy_img_src = $baseurl . '/' . $lazy_img_path;

        $color = $row->settings->lazy_loader_color;

        // set a background color in case the image fails
        $css .= '.fl-node-' . $id . ' > .fl-row-content-wrap { background-color: #' . $color . '; }';
        $css .= '.fl-node-' . $id . ' > .fl-row-content-wrap { background-image: url("' . $lazy_img_src . '"); }';

      }
      
    }

    return $css;
  } 

  /**
   * Filter the css so that lazy loaded images can use a placeholder that doesn't disrupt the flow of the content.
   *
   * @param string $css
   * @param array $nodes
   * @param object $global_settings
   * @return string $css
   */
  public static function filter_module_css(string $css, array $nodes, object $global_settings): string {

    foreach ($nodes['modules'] as $id => $module) {
      $module_class = get_class($module);
      if ($module_class === 'FLPhotoModule') {

        $img_data = $module->get_data();
        $img_sizes_arr = (array)$img_data->sizes;
        $img_src = $module->get_src();
        
        // get the selected size based on the source url of the image
        $selected_size = array_filter($img_sizes_arr, function($size) use ($img_src) {
          if ($size->url === $img_src) {
            return $size;
          }
        });

        // use the padding bottom trick to set the initial height of the container
        $selected_size_atts = array_shift($selected_size);
        $height = $selected_size_atts->height;
        $width = $selected_size_atts->width;
        $padding_bottom = ($height / $width) * 100;

        $css .= '.fl-node-' . $id . ' .fl-photo-content { 
          display: block; 
          height: 0; 
          padding-bottom:' . $padding_bottom . '%; 
          position: relative; 
        }';
        
        $css .= '.fl-node-' . $id . ' img {
          position: absolute;
          top: 0;
          left: 0;
          width: 100%;
          height: 100%;
        }';
      }
    }

    return $css;
  }

  /**
   * For photo modules, modify the html to enable lazy loading
   *
   * @param string $html
   * @param object $module
   * @return string $html
   */
  public static function filter_module_html(string $html, object $module): string {

    if (!property_exists($module->settings, 'lazy_loader') ?? $module->settings->lazy_loader !== 'true') {
      return $html;
    }
    
    $module_class = get_class($module);
 
    switch ($module_class) {
      case 'FLPhotoModule':
        $classes = $module->get_classes();

        // get image data
        $id = $module->get_data()->id;
        $alt = $module->data->alt;
        $lazy_src = wp_get_attachment_image_src($id, 'bb-lazy-load', false)[0];
        $img_meta = wp_get_attachment_metadata($id);
        $img_srcset = wp_get_attachment_image_srcset($id, 'large', $img_meta);
        $img_sizes = wp_get_attachment_image_sizes($id,'large', $img_meta);
        $photo_src = $module->settings->photo_src;

        // create a buffer
        $html = ob_start();

        // add the image data
        include BB_LL_DIR . '/partials/photo-module.php';

        // prepare to return
        $html = ob_get_clean();
        break;
      case 'FLGalleryModule':

        // for now, don't lazy load smugmug photos
        if ($module->settings->source !== 'wordpress') {
          break;
        }

        $photos_array = $module->settings->photo_data;
        // $matches = array();
        // preg_match_all('/<img[^>]+\>/', $html, $matches);

        
        // array_map(function($img) use ($photos_array) {
          
        //   echo "<pre>";
        //   var_dump($img);
        //   var_dump($photos_array);
        //   echo "</pre>";

        //   return $img;
        // }, $matches[0]);

        // array_map(function($id, $data) use ($matches) {

        //   $raw_images = $matches[0];

        //   $img_src = $data->src;

        //   echo "<pre>";
        //   var_dump($id);
        //   var_dump($img_src);
        //   var_dump($raw_images);
        //   echo "</pre>";
        //   return $data;
        // }, array_keys($photos_array), $photos_array);





        ob_start();

        $test = preg_split('/<img[^>]+\>/', $html);
        // echo "<pre>test! ";
        // var_dump($test);
        // echo "</pre>";


          foreach ($test as $key => $value) {
            echo "<pre> $key! ";
            var_dump($value);
            echo "</pre>";
          }


        foreach ($photos_array as $id => $data) {
          $alt = $data->alt;
          $lazy_src = wp_get_attachment_image_src($id, 'bb-lazy-load', false)[0];
          $img_meta = wp_get_attachment_metadata($id);
          $img_srcset = wp_get_attachment_image_srcset($id, 'large', $img_meta);
          $img_sizes = wp_get_attachment_image_sizes($id,'large', $img_meta);
          $photo_src = $data->src;

          // echo "<pre> test! ";
          // var_dump($html);
          // echo "</pre>";


          // $test = preg_match('/<img[^>]+\>/', $html);
          $file = file_get_contents(BB_LL_DIR . '/partials/image.php');

          // str_ireplace('/<img[^>]+\>/', $file, $html);

          $html .= preg_replace('/<img[^>]+\>/', $file, $html);

        }
        
        $html = ob_get_clean();

        
        // $html = ob_start();
        // include BB_LL_DIR . '/partials/gallery-module.php';
        // $html = ob_get_clean();
        break;
      default:
        // exit safely just in case
        return $html;
        break;
    }

    return $html;
  }

  /**
   * Query the database to get id's of images based on their URL
   *
   * @param string $url
   * @return int
   */
  private static function get_id_from_url(string $url): string {

    global $wpdb;
    $query = "SELECT ID FROM {$wpdb->posts} WHERE guid='$url'";
    $id = $wpdb->get_var($query);

    return $id;
  }
}