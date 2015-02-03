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

$GLOBALS['wp_domain_work_plugin_option_keys'] = array(

		/**
		 * Hierarchy level of home_url
		 *
		 * @access private
		 */
		'home_level' => 'wp_domain_work_home_url_hierarchy_level',

		/**
		 * Hierarchy level of WordPress installed directory (site_url) for wp-admin
		 *
		 * @access private
		 */
		'site_level' => 'wp_domain_work_site_url_hierarchy_level',

		/**
		 * Plugin activation
		 *
		 * @access public
		 */
		'use_domains' => 'wp_domain_work_domains_dir_activation',

		/**
		 * 除外する domain
		 *
		 * @access public
		 */
		'excepted_domains' => 'wp_domain_work_domains_excepted_domains',

		/**
		 * @access private
		 */
		'domains' => 'wp_domain_work_registered_domains',

		/**
		 * @access private
		 */
		'class_loaders' => 'wp_domain_work_domain_class_loaders',

		/**
		 * @access private
		 */
		'functions_files' => 'wp_domain_work_domain_functions_files',

		/**
		 * @access private
		 */
		'post_type_supports' => 'wp_domain_work_post_type_supports',

		/**
		 * This option key is nothing but flag for forcibly scan domain directories in plugin settings page
		 * This option will never save on wp-options table.
		 *
		 * @access public
		 */
		'force_dir_scan' => 'wp_domain_work_force_domain_directories_scan',

);

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
require_once dirname( __FILE__ ) . '/inc/utility.php';

/**
 * include classloader file
 */
require_once dirname( __FILE__ ) . '/lib/ClassLoader.php';

/**
 * Register classloader
 */
ClassLoader::register( null, dirname( __FILE__ ) . '/class', ClassLoader::FILENAME_STRTOLOWER | ClassLoader::UNDERBAR_AS_HYPHEN );
ClassLoader::register( 'service',   dirname( __FILE__ ) . '/class', ClassLoader::FILENAME_STRTOLOWER );
ClassLoader::register( 'admin',     dirname( __FILE__ ) . '/class', ClassLoader::UNDERBAR_AS_HYPHEN );
ClassLoader::register( 'module',    dirname( __FILE__ ) . '/class', ClassLoader::UNDERBAR_AS_HYPHEN );
ClassLoader::register( 'property',  dirname( __FILE__ ) . '/class', ClassLoader::UNDERBAR_AS_HYPHEN );
ClassLoader::register( 'wordpress', dirname( __FILE__ ) . '/class', ClassLoader::UNDERBAR_AS_HYPHEN );
ClassLoader::register( 'mimosafa',  dirname( __FILE__ ) . '/lib' );

/**
 * Plugin activation & deactivation
 */
register_activation_hook( __FILE__, 'WP_Domain_Work::activation' );
register_deactivation_hook( __FILE__, 'WP_Domain_Work::deactivation' );

/**
 * Plugin init
 */
WP_Domain_Work::init();
