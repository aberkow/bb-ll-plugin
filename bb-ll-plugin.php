<?php
/*
Plugin Name: Beaver Builder Lazy Loading
Description: Enable lazy loading images with Beaver Builder.
Author: UComm Web Team
Version: 0.0.1
Text Domain: bb-ll-plugin
*/

// rename this file according to the plugin.

if (!defined('WPINC')) {
	die;
}

define( 'BB_LL_DIR', plugin_dir_path(__FILE__) );
define( 'BB_LL_URL', plugins_url('/', __FILE__) );

// select the right composer autoload.php file depending on environment.
if (file_exists(dirname(ABSPATH) . '/vendor/autoload.php')) {
	require_once(dirname(ABSPATH) . '/vendor/autoload.php');
} elseif (file_exists(ABSPATH . 'vendor/autoload.php')) {
	require_once(ABSPATH . 'vendor/autoload.php');
} else {
	require_once('vendor/autoload.php');
}

require_once(ABSPATH . 'wp-admin/includes/plugin.php');
require_once('lib/AdminNotices.php');
require_once('lib/AssetLoader.php');
require_once('lib/ImageHandler.php');
require_once('lib/LazyLoader.php');

$bb_active = is_plugin_active('bb-plugin/fl-builder.php');
$bb_ll_active = is_plugin_active('bb-ll-plugin/bb-ll-plugin.php');

if (!$bb_active && $bb_ll_active) {
	add_action('admin_notices', array('BBLL\Lib\AdminNotices', 'bb_activation_error'));
} elseif ($bb_active && $bb_ll_active) {

	if (!get_option('bb-ll-resize-notice')) {
		// add a a notice to resize images
		add_action('admin_notices', array('BBLL\Lib\AdminNotices', 'bb_resize_notice'));
	}

	// add a low res image size
	add_action('after_setup_theme', array('BBLL\Lib\ImageHandler', 'create_image_sizes'));

	// enqueue scripts
	add_action('wp_enqueue_scripts', array('BBLL\Lib\AssetLoader', 'enqueue_scripts'));

	// filter bb row settings
	add_filter('fl_builder_register_settings_form', array('BBLL\Lib\LazyLoader', 'filter_settings_form'), 10, 	2);

	// filter row and module attributes
	add_filter('fl_builder_row_attributes', array('BBLL\Lib\LazyLoader', 'lazy_loader_atts'), 10, 2);
	add_filter('fl_builder_module_attributes', array('BBLL\Lib\LazyLoader', 'lazy_loader_atts'), 10, 2);

	// filter the css for rows
	add_filter('fl_builder_render_css', array('BBLL\Lib\LazyLoader', 'filter_row_css'), 10, 3);
	// filter the css for modules
	add_filter('fl_builder_render_css', array('BBLL\Lib\LazyLoader', 'filter_module_css'), 10, 3);

	// filter the html for modules
	add_filter('fl_builder_render_module_content', array('BBLL\Lib\LazyLoader', 'filter_module_html'), 10, 2);

}
