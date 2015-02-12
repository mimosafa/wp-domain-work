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
	 * @var null|object \(domain)\properties
	 */
	private static $properties = null;

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
		global $pagenow;
		if ( $this->registered === 'post_type' ) {
			if ( $pagenow === 'post.php' || $pagenow === 'post-new.php' ) {
				\admin\post\post_type_supports::init( $this->registeredName );
				add_action( 'add_meta_boxes', [ $this, 'post_type_meta_boxes' ] );
			} else if ( $pagenow === 'edit.php' ) {
				$this->post_type_columns();
			}
			new \wordpress\admin\save_post( $this->registeredName );
		}
	}

	public function post_type_meta_boxes() {
		if ( ! $props =& $this->_get_properties() ) {
			return;
		}
		if ( isset( $this->meta_boxes ) && is_array( $this->meta_boxes ) && $this->meta_boxes ) {
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
					\admin\meta_boxes\property_meta_box::set( $propName, $prop->getArray() );
				}
			}
		}
	}

	public function post_type_columns() {
		if ( ! isset( $this->columns ) || ! is_array( $this->columns ) || ! $this->columns ) {
			return;
		}
		$_PLT = new \admin\list_table\posts_list_table( $this->registeredName );
		foreach ( $this->columns as $column => $args ) {
			$_PLT->add( $column, $args );
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
