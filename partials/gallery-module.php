
<?php
foreach ($photos_array as $id => $data) {

  $alt = $data->alt;
  $lazy_src = wp_get_attachment_image_src($id, 'bb-lazy-load', false)[0];
  $img_meta = wp_get_attachment_metadata($id);
  $img_srcset = wp_get_attachment_image_srcset($id, 'large', $img_meta);
  $img_sizes = wp_get_attachment_image_sizes($id,'large', $img_meta);
  $photo_src = $data->src;

  include BB_LL_DIR . '/partials/image.php';
  
}
?>