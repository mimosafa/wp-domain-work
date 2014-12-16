<?php
/*
Plugin Name: WP Domain Work
Version: 0.1-alpha
Description: 
Author: mimosafa
Author URI: http://mimosafa.me
Plugin URI: http://mimosafa.me
Text Domain: wp-domain-work
Domain Path: /languages
*/

/**
 * Include version check file
 */
require_once dirname( __FILE__ ) . '/inc/version.php';

// version check !
if ( !requirement_wp_domain_work_plugin() ) {
	return;
}

/**
 * Include utility file
 */
require_once __DIR__ . '/inc/utility.php';

/**
 * include classloader class
 */
require_once __DIR__ . '/lib/ClassLoader.php';

/**
 * Register classloader
 */
# ClassLoader::register( 'admin',     __DIR__ . '/class' );
# ClassLoader::register( 'module',    __DIR__ . '/class' );
# ClassLoader::register( 'property',  __DIR__ . '/class' );
# ClassLoader::register( 'service',   __DIR__ . '/class' );
ClassLoader::register( 'wordpress', __DIR__ . '/class' );

/**
 * Include plugin file
 */
require_once __DIR__ . '/class/wp-domain-work.php';

/**
 * Plugin activation & deactivation
 */
register_activation_hook( __FILE__, 'WP_Domain_Work::activation' );
# register_deactivation_hook( __FILE__, 'WP_Domain_Work::deactivation' );

/**
 * Plugin init
 */
WP_Domain_Work::init();

if ( is_admin() ) {
	WP_Domain_Work::settings_page();
}

/**
 * Initializing domains directory, if activated
 */
# if ( get_option( 'wp_dct_domains_dir_activation' ) ) {
# 	new service\domain\init();
# }

/**
 * Bootstrap theme
 * - validate options
 * - add theme setting page
 */
# require_once __DIR__ . '/inc/theme-setup.php';

/**
 *
 */
# new service\router();


