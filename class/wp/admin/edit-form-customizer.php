<?php

namespace WP_Domain_Work\WP\admin;

class edit_form_customizer {

	/**
	 * Hooked @ admin_head-{hookname} will be recommended.
	 */
	public function kill_submit_box_preview_button() {
		global $post_type;
		if ( ! get_post_type_object( $post_type )->public ) {
			return;
		}
		add_action( 'edit_form_after_title', [ $this, 'post_type_temporary_private' ] );
	}

	public function post_type_temporary_private() {
		global $post_type;
		global $wp_post_types;
		$wp_post_types[$post_type]->public = false;
	}

}
