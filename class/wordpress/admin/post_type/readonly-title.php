<?php

namespace wordpress\admin\post_type;

class readonly_title extends base {

	/**
	 *
	 */
	public function css() {
		echo <<<EOF
<style type="text/css" id="readonly-title-style">
#titlediv #title { background-color: #eee; }
#title:focus { border-color: #ddd; box-shadow: none; }
</style>
EOF;
	}

	/**
	 *
	 */
	public function js() {
		echo <<<EOF
<script type="text/javascript" id="readonly-title-script">
jQuery('#title').attr('readonly','readonly').removeAttr('name');
</script>
EOF;
	}

	/**
	 *
	 */
	public function init() {
		if ( !post_type_supports( $this -> post_type, 'title' ) ) {
			add_action( 'load-post-new.php', [ $this, 'add_support'] );
			add_action( 'admin_action_edit', [ $this, 'add_support'] );
		}

		add_action( 'admin_action_edit', function() {

			$post_id = $_GET['post'];

			if ( current_user_can( 'edit_others_posts', $post_id ) ) {
				return;
			}

			/**
			 * Return if auto-draft or no-title
			 */
			$ttl = get_the_title( $post_id );
			if ( !$ttl || __( 'Auto Draft' ) === $ttl ) {
				return;
			}

			add_action( 'admin_head', [ $this, 'css' ] );
			add_action( 'admin_footer', [ $this, 'js' ] );
		} );
	}

	/**
	 * @access public
	 */
	public function add_support() {
		add_post_type_support( $this -> post_type, 'title' );
	}

	/**
	 *
	 */
	public static function set( $post_type = '' ) {
		if ( !is_admin() || !is_string( $post_type ) ) {
			return;
		}
		$instance = new self( $post_type );
		$instance -> init();
	}

}
