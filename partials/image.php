
<?php 
  // convert data-img-src and data-srcSet to img atts w/ js
?>

<img 
  alt="<?php echo $alt; ?>" 
  class="<?php echo $classes; ?>"
  data-img-src="<?php echo $photo_src; ?>"
  data-srcSet="<?php echo $img_srcset; ?>" 
  itemprop="image" 
  src="<?php echo $lazy_src; ?>" 
  srcset="" 
  sizes="<?php echo $img_sizes; ?>">

<?php
  // if people aren't using js, still give them an image
?>
<noscript>
  <img 
    alt="<?php echo $alt; ?>"
    class="<?php echo $classes; ?>"
    src="<?php echo $photo_src; ?>"
    srcset="<?php echo $img_srcset; ?>"
    sizes="<?php echo $img_sizes; ?>"> 
</noscript>