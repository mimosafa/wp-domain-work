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
		'template_files' => 'wp_domain_work_domain_template_files',

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
require_once dirname( __FILE__ ) . '/inc/wputil.php';

/**
 * include classloader file
 */
require_once dirname( __FILE__ ) . '/lib/ClassLoader.php';

/**
 * Register classloader
 */
ClassLoader::register( null, dirname( __FILE__ ) . '/class', ClassLoader::FILENAME_STRTOLOWER | ClassLoader::FILENAME_UNDERBAR_AS_HYPHEN );
ClassLoader::register( 'service',   dirname( __FILE__ ) . '/class', ClassLoader::FILENAME_STRTOLOWER );
ClassLoader::register( 'admin',     dirname( __FILE__ ) . '/class', ClassLoader::FILENAME_UNDERBAR_AS_HYPHEN | ClassLoader::NAMESPACE_UNDERBAR_AS_HYPHEN );
ClassLoader::register( 'module',    dirname( __FILE__ ) . '/class', ClassLoader::FILENAME_UNDERBAR_AS_HYPHEN );
ClassLoader::register( 'property',  dirname( __FILE__ ) . '/class', ClassLoader::FILENAME_UNDERBAR_AS_HYPHEN );
ClassLoader::register( 'wordpress', dirname( __FILE__ ) . '/class', ClassLoader::FILENAME_UNDERBAR_AS_HYPHEN );
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

function my_custom_post_status(){
	register_post_status( 'unread', array(
		'label'                     => _x( 'Unread', 'post' ),
		'public'                    => true,
		'exclude_from_search'       => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'label_count'               => _n_noop( '未読 <span class="count">(%s)</span>', '未読 <span class="count">(%s)</span>' ),
	) );
}
add_action( 'init', 'my_custom_post_status' );

add_action('admin_footer-post.php', 'jc_append_post_status_list');
function jc_append_post_status_list(){
	global $post;
	$complete = '';
	$label = '';
	if($post->post_type == 'space'){
		if($post->post_status == 'unread'){
			$complete = ' selected="selected"';
			$label = '<span id="post-status-display"> Unread</span>';
		}
		echo '
		<script>
		  jQuery(document).ready(function($){
		    $(\'#post_status\').append(\'<option value="archive" '.$complete.'>Unread</option>\');
		    $(\'.misc-pub-section label\').append("'.$label.'");
		  });
		</script>
		';
	}
}
