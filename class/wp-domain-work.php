<?php
/**
 *
 */
class WP_Domain_Work {
	use \singleton;

	/**
	 * @var WP_Error
	 */
	private static $error;

	/**
	 * WP Domain Work plugin's options
	 *
	 * @var array
	 */
	private static $option_keys;

	/**
	 */
	protected function __construct() {
		self::$option_keys = $GLOBALS['wp_domain_work_plugin_option_keys'];

		/**
		 * add filters & actions
		 */
		add_filter( 'pre_update_option', [ $this, 'pre_update_option' ], 10, 3 );
	}

	/**
	 *
	 */
	public static function activation() {
		//
	}

	/**
	 * @todo need flush_rewrite_rules and remove_cap() ?
	 */
	public static function deactivation() {
		$delOpts = [
			'home_level',
			'site_level',
			'use_domains',
			'domains',
			'class_loaders',
			'functions_files',
			'post_type_supports',
		];
		foreach ( $delOpts as $option ) {
			self::delete_option( $option );
		}
		self::flush_rewrite_rules();
	}

	/**
	 * @access public
	 */
	public static function __callStatic( $name, $args ) {
		$_WPDW = self::getInstance();
		if ( 'get_' === substr( $name, 0, 4 ) ) {
			array_unshift( $args, substr( $name, 4 ) );
			return call_user_func_array( [ $_WPDW, 'get_option' ], $args );
		} else if ( 'update_' === substr( $name, 0, 7 ) ) {
			array_unshift( $args, substr( $name, 7 ) );
			return call_user_func_array( [ $_WPDW, 'update_option' ], $args );
		} else {
			// throw error
		}
	}

	/**
	 * Get row option key, saved in wp-options table
	 *
	 * @access private
	 *
	 * @param  string $option
	 * @return string
	 */
	private function get_option_key( $option ) {
		return array_key_exists( $option, self::$option_keys ) ? self::$option_keys[$option] : false;
	}

	/**
	 * Get plugin's option value
	 *
	 * @access private
	 *
	 * @param  string $option
	 * @return mixed
	 */
	private function get_option( $option, $default = false ) {
		if ( !array_key_exists( $option, self::$option_keys ) ) {
			return; // throw error
		}
		return \get_option( self::$option_keys[$option], $default );
	}

	/**
	 * Update plugin's option
	 *
	 * @access private
	 *
	 * @param  string $option
	 * @param  mixed
	 * @return boolean
	 */
	private function update_option( $option, $newvalue ) {
		if ( !array_key_exists( $option, self::$option_keys ) ) {
			return false;
		}
		return \update_option( self::$option_keys[$option], $newvalue );
	}

	/**
	 * Domains initialize
	 *
	 * @access private
	 *
	 * @uses   service\domains
	 *
	 * @param  boolean $force_scan (optional) if true, domains directories are forcibly scaned.
	 * @return (void)
	 */
	private static function Domains( $force_scan = false ) {
		new \service\Domains( $force_scan );
	}

	/**
	 * Delete plugin's option
	 *
	 * @access private
	 *
	 * @param  string $option
	 * @return boolean
	 */
	private function delete_option( $option ) {
		if ( !array_key_exists( $option, self::$option_keys ) ) {
			return false;
		}
		return \delete_option( self::$option_keys[$option] );
	}

	/**
	 * @access private
	 *
	 * @todo   When un-use_domain, flush rewrite rules does not work well...
	 */
	public function pre_update_option( $value, $option, $old_value ) {
		switch ( $option ) {
			case $this -> get_option_key( 'use_domains' ) :
				if ( $value !== $old_value ) {
					if ( !$value ) {
						self::flush_rewrite_rules(); // does not work well...
					} else {
						self::installed_level();
					}
				}
				break;
			/**
			 * forcibly scan domain directories
			 */
			case $this -> get_option_key( 'force_dir_scan' ) :
				if ( $value ) {
					self::Domains( true );
				}
				$value = $old_value; // never saved on wp-options table
				break;
			/**
			 * domains
			 */
			case $this -> get_option_key( 'domains' ) :
				if ( $value !== $old_value ) {
					$msg = 'Domains are updated ! ';
					$added   = [];
					$updated = [];
					if ( $old_value && is_array( $old_value ) ) {
						foreach ( $value as $domain => $arg ) {
							if ( !array_key_exists( $domain, $old_value ) ) {
								# $added[$domain] = $arg;
								$msg .= '"' . $domain . '" is added. ';
							} else {
								if ( $arg !== $old_value[$domain] ) {
									# $updated[$domain] = $arg;
									$msg .= '"' . $domain . '" is updated. ';
								}
								unset( $old_value[$domain] );
							}
						}
						foreach ( $old_value as $old => $old_arg ) {
							$msg .= '"' . $old . '" is removed. ';
						}
					}
					if ( function_exists( 'add_settings_error' ) ) {
						add_settings_error( 'wp-domain-work', 'update-domains', $msg, 'updated' );
					}
				}
				break;
			case $this -> get_option_key( 'class_loaders' ) :
				break;
		}

		return $value;
	}

	/**
	 * Plugin init
	 */
	public static function init() {
		$_WPDW = self::getInstance();
		self::$error = new \WP_Error();

		if ( is_admin() ) {
			$_WPDW -> permalink_structure();

			/**
			 * Settings page in admin menu
			 */
			$_WPDW -> settings_page();
		}

		/**
		 * init services
		 */
		if ( $_WPDW -> get_option( 'use_domains' ) && \get_option( 'permalink_structure' ) ) {
			self::Domains();
			if ( $_WPDW -> get_option( 'home_level' ) !== false && $_WPDW -> get_option( 'site_level' ) !== false ) {
				new \service\Router();
			}
		}

		/**
		 * Catch error
		 */
		if ( self::$error -> get_error_code() ) {
			add_action( 'admin_menu', [ $_WPDW, 'notice' ] );
		}
	}

	/**
	 * Set wordpress core installed level
	 *
	 * @uses wordpress\installed_level
	 */
	private static function installed_level() {
		$_WPDW = self::getInstance();
		$level = new \wordpress\installed_level();

		if ( false === $_WPDW -> get_option( 'home_level' ) ) {
			$homeLevel = $level -> get_level( 'home' );
			$_WPDW -> update_option( 'home_level', $homeLevel );
		}
		if ( false === $_WPDW -> get_option( 'site_level' ) ) {
			$siteLevel = $level -> get_level( 'site' );
			$_WPDW -> update_option( 'site_level', $siteLevel );
		}
	}

	/**
	 *
	 */
	private function permalink_structure() {
		if ( !$this -> get_option( 'use_domains' ) ) {
			return;
		}
		if ( !\get_option( 'permalink_structure' ) ) {
			self::$error -> add(
				'permalink_structure',
				'Set the permalink to something other than the default.',
				[ '"WP Domain Work" plugin require customized permalink structure.' ]
			);
		}
	}

	/**
	 * Plugin setting page
	 *
	 * @uses wordpress\admin\settings_page
	 */
	public function settings_page() {
		/**
		 * Get instance settings page generator
		 */
		$_PAGE = new \wordpress\admin\plugin\settings_page();

		$top_page_desc = <<<EOF
This is awesome plugin!
EOF;

		/**
		 * Page: WP Domain Work Settings
		 */
		$_PAGE
		-> init( 'wp-domain-work', 'WP Domain Work Settings', 'WP Domain Work' )
			-> description( $top_page_desc )
			-> section( 'default-setting' )
				-> field( 'domains-activation' )
				-> option_name( $this -> get_option_key( 'use_domains' ), 'checkbox', [ 'label' => 'Use domains' ] )
		;
		if ( !$this -> get_option( 'use_domains' ) ) {
			$_PAGE
					-> description( 'ドメインディレクトリーを有効にする場合はチェックを入れてください' )
			;
		} else {
			$_PAGE
					-> description( 'ドメインディレクトリーは有効です' )
				-> field( 'force-directories-search' )
				-> option_name( $this -> get_option_key( 'force_dir_scan' ), 'checkbox' )
			;
		}

		/**
		 * Subpage: Your Domains
		 */
		if ( $this -> get_option( 'use_domains' ) ) {
			$_PAGE
			-> init( 'wp-domains', 'Your Domains' )
				-> html( '<pre>' . var_export( $this -> get_option( 'domains' ), true ) . '<pre>' )
			;
		}

		$_PAGE -> done();
	}

	/**
	 * @access public
	 */
	public static function flush_rewrite_rules() {
		add_action( 'init', function() {
			flush_rewrite_rules();
		}, 99 );
	}

	/**
	 * Show error message
	 */
	public function notice() {
		$codes = self::$error -> get_error_codes();
		foreach ( $codes as $code ) {
			$msg  = self::$error -> get_error_message( $code );
			$data = self::$error -> get_error_data( $code );
			foreach ( $data as $d ) {
				$msg .= ' ' . $d;
			}
			add_settings_error( 'wp-domain-work', $code, $msg, 'error' );
		}
	}

}
