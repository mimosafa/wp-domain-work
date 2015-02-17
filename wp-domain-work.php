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
if ( ! requirement_wp_domain_work_plugin() ) {
	return;
}

/**
 * Include utility file
 */
require_once dirname( __FILE__ ) . '/inc/utility.php';

/**
 * include classloader file
 */
require_once dirname( __FILE__ ) . '/lib/ClassLoader.php';

/**
 * Register classloader
 */
ClassLoader::register( 'WP_Domain_Work', dirname( __FILE__ ) . '/class',
	ClassLoader::FILENAME_STRTOLOWER | ClassLoader::FILENAME_UNDERBAR_AS_HYPHEN | ClassLoader::NAMESPACE_STRTOLOWER |
	ClassLoader::NAMESPACE_UNDERBAR_AS_HYPHEN | ClassLoader::REMOVE_FIRST_NAMESPACE_STRING );
ClassLoader::register( '', dirname( __FILE__ ) . '/lib' );
ClassLoader::register( 'mimosafa',  dirname( __FILE__ ) . '/lib' );

/**
 * Plugin activation & deactivation
 */
register_activation_hook( __FILE__, 'WP_Domain_Work\Plugin::activation' );
register_deactivation_hook( __FILE__, 'WP_Domain_Work\Plugin::deactivation' );

/**
 * Plugin init
 */
WP_Domain_Work\Plugin::init();








// TESTs below !!!

/**
 * @see https://plugins.trac.wordpress.org/browser/taxonomy-terms-order/tags/1.4.0/taxonomy-terms-order.php#L126
 */
add_filter( 'get_terms_orderby', function( $orderby, $args ) {
	_var_dump( $orderby );
	_var_dump( $args );
	#return $orderby;
	return 't.term_order';
}, 10, 2 );
