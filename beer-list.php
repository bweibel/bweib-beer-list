<?php
/**
 * Plugin Name: Beer List
 * Description: A brewery beer list plugin for managing and displaying your beer catalog.
 * Version: 1.0.0
 * Author: Bweib
 * Text Domain: beer-list
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'BEER_LIST_VERSION', '1.0.0' );
define( 'BEER_LIST_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'BEER_LIST_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once BEER_LIST_PLUGIN_DIR . 'includes/post-types.php';
require_once BEER_LIST_PLUGIN_DIR . 'includes/taxonomies.php';
require_once BEER_LIST_PLUGIN_DIR . 'includes/meta-fields.php';
require_once BEER_LIST_PLUGIN_DIR . 'includes/blocks.php';
require_once BEER_LIST_PLUGIN_DIR . 'includes/settings.php';
