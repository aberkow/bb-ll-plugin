<?php

namespace BBLL\Lib;

class AdminNotices {
  /**
   * Display an admin error if this plugin is active but BB is not.
   *
   * @return void
   */
  public static function bb_activation_error() {
    require_once(BB_LL_DIR . '/partials/bb-admin-error.php');
  }
  /**
   * Display an admin warning to remind people to resize old images
   *
   * @return void
   */
  public static function bb_resize_notice() {
    if (!get_option('bb-ll-resize-notice')) {
      require_once(BB_LL_DIR . '/partials/bb-admin-resize-notice.php');
      add_option('bb-ll-resize-notice');
      update_option('bb-ll-resize-notice', true);
    } 
  }
}