<?php

namespace wordpress\admin\post_type;

class base {

	/**
	 * @var string
	 */
	protected $post_type;

	/**
	 * @access protected
	 */
	protected function __construct( $post_type ) {
		if ( '' !== $post_type ) {
			$this -> post_type = $post_type;
		} else if ( array_key_exists( 'post_type', $_GET ) ) {
			$this -> post_type = $_GET['post_type'];
		} else {
			add_action( 'load-post-new.php', [ $this, 'get_post_type' ], 10 );
			add_action( 'admin_action_edit', [ $this, 'get_post_type' ], 10 );
		}
	}

	/**
	 * Define post_type
	 */
	public function get_post_type() {
		$this -> post_type = get_current_screen() -> post_type;
	}

}
