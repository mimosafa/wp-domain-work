<?php

namespace wordpress\admin\post_type;

/**
 * Usage: \wordpress\admin\readonly_slug::set( 'cpt' );
 */
class readonly_slug extends base {

	/**
	 *
	 */
	public function init() {
		add_action( 'admin_action_edit', [ $this, 'admin_action_edit' ], 11 );
	}

	/**
	 *
	 */
	public function admin_action_edit() {
		if ( !post_type_exists( $this -> post_type ) ) {
			return;
		}

		$post_id = $_GET['post'];
		if ( current_user_can( 'edit_others_posts', $post_id ) ) {
			return;
		}

		/**
		 * @see http://ja.forums.wordpress.org/topic/21239
		 */
		add_filter( 'get_sample_permalink_html', [ $this, 'sample_permalink_html' ], 10, 2 );
		add_action( 'add_meta_boxes', function() {
			remove_meta_box( 'slugdiv', $this -> post_type, 'normal' );
		} );
	}

	/**
	 * @see https://core.trac.wordpress.org/browser/trunk/src/wp-admin/includes/post.php#L1184
	 */
	public function sample_permalink_html( $return, $id ) {
		if ( 'publish' !== get_post_status( $id ) ) {
			return $return;
		}
		if ( current_user_can( 'read_post', $id ) ) {
			$ptype = get_post_type_object( $this -> post_type );
			if( 'draft' == get_post_status( $id ) ) {
				$view_post = __( 'Preview' );
			} else {
				$view_post = $ptype -> labels -> view_item;
			}
		}
		$return  = '<strong>' . __('Permalink:') . "</strong>\n";
		$return .= '<span id="sample-permalink" tabindex="-1">' . get_permalink( $id ) . "</span>\n";
		$return .= "<span id='view-post-btn'><a href='" . get_permalink( $id ) . "' class='button button-small'>$view_post</a></span>\n";

		return $return;
	}

	/**
	 * Wrapper static function
	 *
	 * @access public
	 *
	 * @param  string $post_type (optional)
	 * @return (void)
	 */
	public static function set( $post_type = '' ) {
		if ( !is_admin() || !is_string( $post_type ) ) {
			return;
		}
		$instance = new self( $post_type );
		$instance -> init();
	}

}
