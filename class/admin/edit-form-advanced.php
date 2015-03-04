<?php

namespace WP_Domain_Work\Admin;

class edit_form_advanced {
	use \WP_Domain_Work\Utility\Singleton;

	private $forms = [
		'edit_form_top'              => [],
		'edit_form_before_permalink' => [],
		'edit_form_after_title'      => [],
		'edit_form_after_editor'     => [],
	];

	protected function __construct() {
		add_action( 'dbx_post_advanced', [ $this, 'init' ] );
	}

	public function init() {
		$this->forms = array_filter( $this->forms );
		if ( $this->forms ) {

		}
	}

	public static function __callStatic( $name, $args ) {
		if ( 'set_' !== substr( $name, 0, 4 ) ) {
			return false;
		}
		$self = self::getInstance();
		$hook = substr( $name, 4 );
		if ( array_key_exists( $hook, $self->forms ) ) {
			$self->forms[$hook][] = $args;
		}
	}

	// 何かが違う…

}
