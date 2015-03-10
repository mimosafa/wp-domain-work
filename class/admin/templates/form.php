<?php

namespace WP_Domain_Work\Admin\templates;

/**
 * @uses WP_Domain_Work\WP\nonce
 * @uses mimosafa\Decoder
 * @uses WP_Domain_Work\Property\(property type)
 */
class form {

	/**
	 * @var string
	 */
	protected $context;

	/**
	 * @var bool
	 */
	protected $_post_new;

	/**
	 * @var object WP_Domain_Work\WP\nonce
	 */
	protected static $nonceInstance;

	/**
	 * @var object mimosafa\Decoder
	 */
	protected static $decoder = null;

	/**
	 * Constructor
	 */
	protected function __construct( $context ) {
		if ( ! $context || ! is_string( $context ) ) {
			return;
		}
		$this->context = $context;
		$this->_post_new = ( 'add' === get_current_screen()->action ) ? true : false;
		self::$nonceInstance = new \WP_Domain_Work\WP\nonce( $context );
	}

	public static function getInstance( $context ) {
		static $instance = null;
		if ( $instance && $instance->context === $context ) {
			return $instance;
		}
		$cl = __CLASS__;
		return new $cl( $context );
	}

	public function generate( $args ) {
		if ( ! array_key_exists( '_type', $args ) || ! array_key_exists( 'name', $args ) ) {
			return; // error
		}
		$type = $args['_type'];
		if ( $type === 'post_children' ) {
			$this->generate_children_list_table( $args );
		} else {
			$dom_array = $this->generate_dom_array( $args );
			if ( empty( $dom_array ) ) {
				return;
			}
			if ( ! self::$decoder ) {
				self::$decoder = new \mimosafa\Decoder();
			}
			var_dump( $dom_array );
		}
	}

	protected function generate_children_list_table( $args ) {
		#echo '<pre>'; var_dump( $args ); echo '</pre>';
		$table = new \WP_Domain_Work\Admin\list_table\Post_Children_List_Table( $args );
		$table->prepare_items();
		$table->display();
	}

	protected function generate_dom_array( $args ) {
		$type = $args['_type'];
		$return = [];
		//
	}

}
