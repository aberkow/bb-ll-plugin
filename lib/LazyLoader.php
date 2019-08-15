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
      'label' => __('Lazy Loaded Image', 'uconn-2019'),
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


    if ($id === 'row') {
        $form['tabs']['style']['sections']['bg_photo']['fields']['lazy_loader'] = $lazy_loader;
        $form['tabs']['style']['sections']['bg_photo']['fields']['lazy_loader_color'] = $lazy_loader_bg;
    }

    if ($id === 'photo') {
      $form['general']['sections']['general']['fields']['lazy_loader'] = $lazy_loader;
      $form['general']['sections']['general']['fields']['lazy_loader_color'] = $lazy_loader_bg;
    }


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
        
        $img_url = $row->settings->bg_image_src;
        $img_id = self::get_id_from_url($img_url);
  

        $img_meta = wp_get_attachment_metadata($img_id);

        $lazy_img_path = substr_replace($img_meta['file'], $img_meta['sizes']['lazy-load']['file'], 8);

        // this path may need to be cleared up
        // is it always /content?
        $lazy_img_src = WP_CONTENT_URL . '/uploads/' . $lazy_img_path;

        $color = $row->settings->lazy_loader_color;
        $css .= '.fl-node-' . $id . ' > .fl-row-content-wrap { background-color: #' . $color . '; }';
        // set a transparent gradient
        // this way the bg image won't be shown since this will get priority
        // $css .= '.fl-node-' . $id . ' > .fl-row-content-wrap { background-image: linear-gradient(transparent, transparent); }';
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

        $css .= '.fl-node-' . $id . ' > .fl-module-content { 
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

    $module_class = get_class($module);

    if ($module_class !== 'FLPhotoModule' || $module->settings->lazy_loader !== 'true') {
      return $html;
    }

    $classes = $module->get_classes();

    // get image data
    $id = $module->get_data()->id;
    $lazy_src = wp_get_attachment_image_src($id, 'bb-lazy-load', false)[0];
    $img_meta = wp_get_attachment_metadata($id);
    $img_srcset = wp_get_attachment_image_srcset($id, 'large', $img_meta);
    $img_sizes = wp_get_attachment_image_sizes($id,'large', $img_meta);

    // convert data-img-src and data-srcSet to img atts w/ js
    $html = "<img src='" . $lazy_src . "' srcset='' data-img-src='" . $module->settings->photo_src . "' data-srcSet='" . $img_srcset . "' sizes='" . $img_sizes . "' alt='" . $module->data->alt . "' class='" . $classes . "'/>";

    // if people aren't using js, still give them an image
    $html .= "<noscript>";
    $html .= "<img src='" . $module->settings->photo_src . "' srcset='" . $img_srcset . "' sizes='" . $img_sizes . "' alt='" . $module->data->alt . "' class='" . $classes . "'/>";
    $html .= "</noscript>";

    return $html;
  }
  /**
   * Query the database to get id's of images based on their URL
   *
   * @param string $url
   * @return int
   */
  private static function get_id_from_url(string $url): id {

    global $wpdb;
    $query = "SELECT ID FROM {$wpdb->posts} WHERE guid='$url'";
    $id = $wpdb->get_var($query);

    return $id;
  }
}