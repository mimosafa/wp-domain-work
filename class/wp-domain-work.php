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
	}

	/**
	 *
	 */
	public static function activation() {
		self::installed_level();
	}

	/**
	 * @todo need flush_rewrite_rules and remove_cap() ?
	 */
	public static function deactivation() {
		$delOpts = [ 'home_level', 'site_level', 'domains', 'class_loaders', 'functions_files' ];
		foreach ( $delOpts as $option ) {
			self::delete_option( $option );
		}
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
			new \service\Domains();
			if ( $_WPDW -> get_option( 'home_level' ) !== false && $_WPDW -> get_option( 'site_level' ) !== false ) {
				new \service\Router();
			}
		}

		/**
		 * Catch error
		 */
		if ( self::$error -> get_error_code() ) {
			add_action( 'admin_notices', [ $_WPDW, 'notice' ] );
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
			;
		}
		$_PAGE
				-> field( 'force-directories-search' )
				-> html( '<p>Hello!</p>' )
				-> html( 'World', true )
		;

		if ( $this -> get_option( 'use_domains' ) ) {
			$_PAGE
			-> init( 'wp-domains', 'Your Domains' )
				-> html( '<pre>' . var_export( $this -> get_option( 'domains' ), true ) . '<pre>' )
			;
		}

		$_PAGE -> done();
	}

	/**
	 * Show error message
	 */
	public function notice() {
		$codes = self::$error -> get_error_codes();
		foreach ( $codes as $code ) {
			$msg  = self::$error -> get_error_message( $code );
			$data = self::$error -> get_error_data( $code );
			?>
  <div class="message error">
    <p>
      <b><?= esc_html( $msg ) ?></b> <?= esc_html( $data[0] ) ?>
    </p>
  </div>
			<?php
		}
	}

}
