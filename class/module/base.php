<?php

namespace module;

trait base {

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
	 * Get domain's setting defined at properties.php (stored in option table as 'wp_dct_domains')
	 * 
	 * @access protected
	 */
	protected function _domain_settings() {
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

	/**
	 * @return \(domain)\properties
	 */
	protected function &_get_properties() {
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
