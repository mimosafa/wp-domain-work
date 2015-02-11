<?php

namespace admin\meta_boxes;

class property_meta_box {
	use \singleton;

	private $post_type;

	private static $meta_boxes = [];

	/**
	 * 
	 */
	private $_box_id_prefix = 'wp-domain-work-meta-box-';

	/**
	 * @var null|object \wordpress\admin\meta_box_inner
	 */
	private static $metaBoxInner = null;

	/**
	 *
	 */
	protected function __construct() {
		if ( ! $this->post_type = get_post_type() ) {
			return false;
		}
		add_action( 'add_meta_boxes_' . $this->post_type, [ $this, 'init' ] );
	}

	public function init() {
		if ( ! self::$meta_boxes ) {
			return;
		}
		foreach ( self::$meta_boxes as $meta_box ) {
			call_user_func_array( 'add_meta_box', $meta_box );
		}
	}

	public static function set( $name, $args = [] ) {
		if ( ! is_string( $name ) || ! $name ) {
			return false;
		}
		$_PMB = self::getInstance();

		$post_type = $_PMB->post_type;
		$id = esc_attr( $_PMB->_box_id_prefix . $name );
		$title = esc_html( $args['label'] );
		if ( array_key_exists( 'callback', $args ) && is_callable( $args['callback'] ) ) {
			$callback = $args['callback'];
		} else {
			$inner = \admin\templates\meta_box_inner::getInstance( $post_type );
			$callback = [ $inner, 'init' ];
		}
		static $_contexts = [ 'normal', 'advanced', 'side' ];
		$context = array_key_exists( 'context', $args ) && in_array( $args['context'], $_contexts )
			? $args['context']
			: 'advanced'
		;
		static $_priorities = [ 'high', 'core', 'default', 'low' ];
		$priority = array_key_exists( 'priority', $args ) && in_array( $args['priority'], $_priorities )
			? $args['priority']
			: 'default'
		;
		$callback_args = $args;

		self::$meta_boxes[] = compact(
			'id', 'title', 'callback', 'post_type',
			'context', 'priority', 'callback_args'
		);
	}

}
