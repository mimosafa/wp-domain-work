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
		$this->init();
	}

	public function init() {
		foreach ( array_keys( $this->forms ) as $hook ) {
			add_action( $hook, [ $this, 'output' ] );
		}
	}

	public static function set( $hook, $args ) {
		$self = self::getInstance();
		if ( array_key_exists( $hook, $self->forms ) ) {
			$self->forms[$hook][] = $args;
		}
	}

	public function output() {
		foreach ( array_keys( $this->forms ) as $hook ) {
			if ( doing_action( $hook ) ) {
				break;
			}
		}
		if ( ! $this->forms[$hook] ) {
			return;
		}
		foreach ( $this->forms[$hook] as $args ) {
			// echo '<pre>'; var_dump( $args ); echo '</pre>';
			if ( ! array_key_exists( 'callback', $args ) || ! is_callable( $args['callback'] ) ) {
				global $post_type;
				$gen = \WP_Domain_Work\Admin\templates\form::getInstance( $post_type );
				$gen->generate( $args );
			} else {
				$func = $args['callback'];
				unset( $args['callback'] );
				call_user_func( $func, $args );
			}
		}
		unset( $this->forms[$hook] );
	}

}
