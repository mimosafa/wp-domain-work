<?php

namespace WP_Domain_Work\Module;

trait base {
	use \WP_Domain_Work\Utility\classname;

	/**
	 * @var string Dmain's name.
	 */
	protected $domain;

	/**
	 * @var string post_type|taxonomy
	 */
	protected $registered;

	/**
	 * @var string Registered name of domain on system.
	 */
	protected $registeredName;

	/**
	 * @var null|object \(domain)\properties
	 */
	protected static $properties = null;

	protected static $falseVal = false;

	/**
	 * constructor
	 *
	 * @access public
	 */
	public function __construct() {
		$this->_domain_settings();
		$this->init();
	}

	protected function init() {
		//
	}

	/**
	 * Get domain's setting defined at properties.php (stored in option table as 'wp_dct_domains')
	 * 
	 * @access protected
	 */
	protected function _domain_settings() {
		/**
		 * Define domain's name by namespace string
		 * @uses \WP_Domain_Work\Utility\classname::getNamespace
		 */
		$domainNS = self::getNamespace( $this );
		$this->domain = substr( $domainNS, strripos( $domainNS, '\\' ) + 1 );

		/**
		 * Get domain's setting stored in option table
		 * @var array
		 */
		$setting = \WP_Domain_Work\Plugin::get_domains()[$this->domain];

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

	/**
	 * @return \(domain)\properties
	 */
	protected function &_get_properties() {
		if ( ! self::$properties ) {
			$className = sprintf( 'WP_Domain\\%s\\properties', $this->domain );
			if ( ! class_exists( $className ) ) {
				return self::$falseVal;
			}
			self::$properties = new $className();
		}
		return self::$properties;
	}

}
