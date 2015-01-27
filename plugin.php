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
 * System version check
 */
require_once dirname( __FILE__ ) . '/inc/version.php';
if ( !requirement_wp_domain_work_plugin() ) {
	return;
}

/**
 * Include utility file
 */
require_once __DIR__ . '/inc/utility.php';

/**
 * include classloader file
 */
require_once __DIR__ . '/lib/ClassLoader.php';

/**
 * Register classloader
 */
ClassLoader::register( null, __DIR__ . '/class', ClassLoader::FILENAME_STRTOLOWER | ClassLoader::UNDERBAR_AS_HYPHEN );
ClassLoader::register( 'service', __DIR__ . '/class', ClassLoader::FILENAME_STRTOLOWER );
# ClassLoader::register( 'admin',     __DIR__ . '/class' );
ClassLoader::register( 'module',    __DIR__ . '/class', ClassLoader::UNDERBAR_AS_HYPHEN );
ClassLoader::register( 'property',  __DIR__ . '/class', ClassLoader::UNDERBAR_AS_HYPHEN );
ClassLoader::register( 'wordpress', __DIR__ . '/class', ClassLoader::UNDERBAR_AS_HYPHEN );
ClassLoader::register( 'mimosafa',  __DIR__ . '/lib' );

/**
 * Plugin activation & deactivation
 */
register_activation_hook( __FILE__, 'WP_Domain_Work::activation' );
# register_deactivation_hook( __FILE__, 'WP_Domain_Work::deactivation' );

/**
 * Plugin init
 */
WP_Domain_Work::init();
