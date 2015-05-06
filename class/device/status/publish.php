<?php
namespace WPDW\Device\Status;

class publish {

	private static $defaults = [
		'name'        => '%s',
		'description' => '%s',
		'action'      => '%s',
		'published_on' => '%s on:',
		'publish_on'   => '%s on:',
		'publish_immediately' => '%s <b>immediately</b>' 
	];

	private $texts = [

		'Published' => [
			'post' => '{{name}}',
			'{{name}}',
		],

		'Published on: <b>%1$s</b>' => [
			'{{published_on}} <b>%1$s</b>',
		],

		'Publish <b>immediately</b>' => [
			'{{publish_immediately}}'
		],

		'Publish on: <b>%1$s</b>' => [
			'{{publish_on}} <b>%%1$s</b>'
		],

		'Publish' => [
			'{{action}}'
		],
	];

	public static function get_filter_definition() {
		static $definition;
		if ( ! $definition ) {
			$definition = array_map( function( $var ) {
				return $var === \FILTER_SANITIZE_FULL_SPECIAL_CHARS;
			}, self::$defaults );
		}
		return $definition;
	}

	public static function get_defaults( $label ) {
		if ( ! $label = filter_var( $label ) )
			return;
		$defaults = [];
		foreach ( self::$defaults as $key => $val ) {
			$defaults[$key] = sprintf( __( $val ), $label );
		}
		return $defaults;
	}

	public function __construct( Array $labels ) {
		if ( $labels = filter_var_array( $labels, $this->get_filter_definition() ) ) {
			array_walk_recursive( $this->texts, [ $this, 'prepare_texts' ], $labels );
			$this->texts = array_filter( $this->texts );
		}
	}

	private function prepare_texts( &$str, $text, Array $labels ) {
		preg_match( '/\{\{([a-z_]+)\}\}/', $str, $m );
		$key = $m[1];
		if ( isset( $labels[$key] ) ) {
			static $callback;
			if ( ! $callback ) {
				$callback = function( $m ) use ( $labels ) {
					return $labels[$m[1]];
				};
			}
			$str = preg_replace_callback( '/\{\{([a-z_]+)\}\}/', $callback, $str );
		} else {
			$str = null;
		}
	}

	private function init() {
		add_action( 'current_screen', [ $this, 'set_gettext' ] );
		add_action( 'admin_footer',   [ $this, 'reset_gettext' ] );
		add_action( 'load-post.php',     [ $this, 'init_postpage' ] );
		add_action( 'load-post-new.php', [ $this, 'init_postpage' ] );
		add_action( 'load-edit.php',     [ $this, 'init_listpage' ] );
	}

	public function set_gettext() {
		add_filter( 'gettext', [ $this, 'gettext' ], 10, 2 );
		add_filter( 'gettext_with_context', [ $this, 'gettext_with_context' ], 10, 3 );
	}

	public function reset_gettext() {
		remove_filter( 'gettext', [ $this, 'gettext' ] );
		remove_filter( 'gettext_with_context', [ $this, 'gettext_with_context' ] );
	}

	public function gettext( $translated, $text ) {
		if ( array_key_exists( $text, $this->texts ) )
			$translated = $this->texts( $text );
		return $translated;
	}

	public function gettext_with_context( $trabslated, $text, $context ) {
		//
		return $translated;
	}

	public function init_postpage() {
		//
	}

	public function init_listpage() {
		//
	}

}
