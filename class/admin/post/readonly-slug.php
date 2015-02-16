<?php

namespace WP_Domain_Work\Admin\post;

class readonly_slug {

	private $post_type;

	public function __construct( $post_type ) {
		if ( is_string( $post_type ) && $post_type ) {
			$this->post_type = $post_type;
			$this->init();
		}
	}

	private function init() {
		add_action( 'admin_action_edit', [ $this, 'admin_action_edit' ] );
	}

	public function admin_action_edit() {
		if ( get_current_screen()->post_type !== $this->post_type ) {
			return;
		}
		$post_id = $_GET['post'];
		if ( get_post_status( $post_id ) !== 'publish' ) {
			return;
		}
		/*
		if ( current_user_can( 'edit_others_posts', $post_id ) ) {
			return;
		}
		*/
		add_filter( 'get_sample_permalink_html', [ $this, 'sample_permalink_html' ], 10, 2 );
		/**
		 * @see http://ja.forums.wordpress.org/topic/21239
		 */
		add_action( 'add_meta_boxes_' . $this->post_type, function() {
			remove_meta_box( 'slugdiv', $this->post_type, 'normal' );
		} );
	}

	/**
	 * @see https://core.trac.wordpress.org/browser/trunk/src/wp-admin/includes/post.php#L1184
	 */
	public function sample_permalink_html( $return, $id ) {
		if ( current_user_can( 'read_post', $id ) ) {
			$ptype = get_post_type_object( $this->post_type );
			if( 'draft' == get_post_status( $id ) ) {
				$view_post = __( 'Preview' );
			} else {
				$view_post = $ptype->labels->view_item;
			}
		}
		$return  = '<strong>' . __('Permalink:') . "</strong>\n";
		$return .= '<span id="sample-permalink" tabindex="-1">' . get_permalink( $id ) . "</span>\n";
		$return .= "<span id='view-post-btn'><a href='" . get_permalink( $id ) . "' class='button button-small'>$view_post</a></span>\n";
		return $return;
	}

}
