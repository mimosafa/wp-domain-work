<?php

namespace module;

/**
 * This is 'Trait',
 * must be used in '\(domain)\admin' class.
 *
 * @uses
 */
trait admin {

	/**
	 * @var string Dmain's name.
	 */
	private $domain;

	/**
	 * @var string post_type|taxonomy
	 */
	private $registered;

	/**
	 * @var string Registered name of domain on system.
	 */
	private $registeredName;

	/**
	 * 
	 */
	private $_box_id_prefix = 'wp-domain-work-meta-box-';

	/**
	 * @var null|object \(domain)\properties
	 */
	private static $properties = null;

	/**
	 * @var null|object \wordpress\admin\meta_box_inner
	 */
	private static $metaBoxInner = null;

	private static $falseVal = false;

	/**
	 * constructor
	 *
	 * @access public
	 */
	public function __construct() {
		$this->_domain_settings();
		$this->init();
	}

	/**
	 * Get domain's setting defined at properties.php (stored in option table as 'wp_dct_domains')
	 * 
	 * @access private
	 */
	private function _domain_settings() {
		/**
		 * define domain's name
		 *
		 * @uses \utility\getObjectNamespace
		 */
		$this->domain = \utility\getObjectNamespace( $this );

		/**
		 * Get domain's setting stored in option table
		 *
		 * @var array
		 */
		$setting = \WP_Domain_Work::get_domains()[$this->domain];

		// Identify 'post_type' or 'taxonomy'
		switch ( $setting['register'] ) {
			case 'Custom Post Type' :
				$this->registered = 'post_type';
				break;
			case 'Custom Taxonomy' :
				$this->registered = 'taxonomy';
				break;
		}
		// Confirm registered name
		if ( array_key_exists( $this->registered, $setting ) ) {
			$this->registeredName = $setting[$this->registered];
		} else {
			$this->registeredName = $this->domain;
		}
	}

	public function init() {
		if ($this->registered === 'post_type') {
			\admin\post\post_type_supports::init( $this->registeredName );
			add_action( 'add_meta_boxes', [ $this, 'post_type_meta_boxes' ] );
			new \wordpress\admin\save_post( $this->registeredName );
		}
	}

	public function post_type_meta_boxes() {
		if ( ! $props =& $this->_get_properties() ) {
			return;
		}
		if ( isset( $this->meta_boxes ) && $this->meta_boxes ) {
			foreach ( $this->meta_boxes as $arg ) {
				if ( is_string($arg) && $prop = $props->$arg ) {
					if ( in_array( $arg, [ 'post_parent', 'menu_order' ] ) ) {
						\admin\meta_boxes\attributes_meta_box::set( $arg, $prop->getArray() );
					} else {
						//
					}
				} else if ( is_array( $arg ) ) {
					if ( ! array_key_exists( 'property', $arg ) ) {
						continue;
					}
					$propName = $arg['property'];
					if ( ! $prop = $props->$propName ) {
						continue;
					}
					$id = esc_attr( $this->_box_id_prefix . $this->domain . '-' . $propName );
					$title = esc_html( $prop->label );
					if ( array_key_exists( 'callback', $arg ) && is_callable( $arg['callback'] ) ) {
						$callback = $arg['callback'];
					} else {
						if ( null === self::$metaBoxInner ) {
							self::$metaBoxInner = new \wordpress\admin\meta_box_inner( $this->registeredName );
						}
						$callback = [ self::$metaBoxInner, 'init' ];
					}
					$post_type = $this->registeredName;
					static $_contexts = [ 'normal', 'advanced', 'side' ];
					$context = array_key_exists( 'context', $arg ) && in_array( $arg['context'], $_contexts )
						? $arg['context']
						: 'advanced'
					;
					static $_priorities = [ 'high', 'core', 'default', 'low' ];
					$priority = array_key_exists( 'priority', $arg ) && in_array( $arg['priority'], $_priorities )
						? $arg['priority']
						: 'default'
					;
					$callback_args = [ 'instance' => $prop ];

					$meta_box = compact(
						'id', 'title', 'callback', 'post_type',
						'context', 'priority', 'callback_args'
					);
					call_user_func_array( 'add_meta_box', $meta_box );
				}
			}
		}
	}

	/**
	 * @return \(domain)\properties
	 */
	private function &_get_properties() {
		if ( ! self::$properties ) {
			$className = sprintf( '\\%s\\properties', $this->domain );
			if ( ! class_exists( $className ) ) {
				return self::$falseVal;
			}
			self::$properties = new $className();
		}
		return self::$properties;
	}

}
