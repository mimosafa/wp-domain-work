<?php

namespace WP_Domain_Work\Admin\post;

class readonly_title {

	private $post_type;

	public function __construct( $post_type ) {
		if ( is_string( $post_type ) && $post_type ) {
			$this->post_type = $post_type;
			$this->init();
		}
	}

	private function init() {
		if ( ! post_type_supports( $this->post_type, 'title' ) ) {
			add_action( 'load-edit.php',     [ $this, 'add_support'] );
			add_action( 'load-post-new.php', [ $this, 'add_support'] );
			add_action( 'admin_action_edit', [ $this, 'add_support'] );
		}
		add_action( 'admin_action_edit', [ $this, 'admin_action_edit' ] );
	}

	public function add_support() {
		if ( ! post_type_exists( $this->post_type ) ) {
			return;
		}
		add_post_type_support( $this->post_type, 'title' );
	}

	public function admin_action_edit() {
		if ( get_current_screen()->post_type !== $this->post_type ) {
			return;
		}
		$post_id = $_GET['post'];
		if ( get_post_status( $post_id ) !== 'publish' ) {
			return;
		}
		if ( current_user_can( 'edit_others_posts', $post_id ) ) {
			# return; // -------------------------------------------------------- 開発中のためコメントアウト
		}
		$ttl = get_the_title( $post_id );
		if ( !$ttl || __( 'Auto Draft' ) === $ttl ) {
			return; // Return if auto-draft or no-title
		}
		add_action( 'admin_head', [ $this, 'css' ] );
		add_action( 'admin_footer', [ $this, 'js' ] );
	}

	public function css() {
		echo <<<EOF
<style type="text/css" id="readonly-title-style">
#titlediv #title { background-color: #eee; }
#title:focus { border-color: #ddd; box-shadow: none; }
</style>
EOF;
	}

	public function js() {
		echo <<<EOF
<script type="text/javascript" id="readonly-title-script">
jQuery('#title').attr('readonly','readonly').removeAttr('name');
</script>
EOF;
	}

}
