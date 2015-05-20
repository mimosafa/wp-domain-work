<?php
/*
Plugin Name: WP Domain Work
Version: 0.1-alpha
Description: Manage custom post type, custom taxonomy, and custom field by directory and file base.
Author: Toshimichi Mimoto
Author URI: http://mimosafa.me
Plugin URI: https://github.com/mimosafa/wp-domain-work
Text Domain: wp-domain-work
License: GPL2 or later
Domain Path: /languages
*/

define( 'WPDW_PLUGIN_FILE', __FILE__ );
define( 'WPDW_PLUGIN_DIR', dirname( WPDW_PLUGIN_FILE ) );
define( 'WPDW_PLUGIN_URL', plugins_url( '', __FILE__ ) );
define( 'WPDW_VERSION', '0.1' );

// Version requirement
require_once dirname( __FILE__ ) . '/inc/version.php';
if ( ! requirement_wp_domain_work_plugin() )
	return;

// Initialize plugin
require_once dirname( __FILE__ ) . '/bootstrap.php';
initialize_wp_domain_work_plugin();
