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
require_once dirname( __FILE__ ) . '/inc/utility.php';

/**
 * include classloader file
 */
require_once dirname( __FILE__ ) . '/lib/ClassLoader.php';

#require_once dirname( __FILE__ ) . '/lib/Singleton.php';

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

/*
function my_custom_post_status(){
	register_post_status( 'unread', array(
		'label'                     => _x( 'Unread', 'post' ),
		'public'                    => true,
		'exclude_from_search'       => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'label_count'               => _n_noop( 'Unread <span class="count">(%s)</span>', 'Unread <span class="count">(%s)</span>' ),
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
		echo <<<EOF
<script>
  jQuery(document).ready(function($){
    $('#post_status').append('<option value="archive"{$complete}>Unread</option>');
    $('.misc-pub-section label').append('{$label}');
  });
</script>
EOF;
	}
}
*/
