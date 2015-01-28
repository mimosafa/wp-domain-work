<?php
/**
 *
 */
class WP_Domain_Work {
	use \singleton;

	/**
	 * @var WP_Error
	 */
	private static $error = null;

	/**
	 * WP Domain Work plugin's options
	 *
	 * @var array
	 */
	private static $options = [

		/**
		 * Hierarchy level of home_url
		 *
		 * @access private
		 */
		'home_level' => [
			'key' => 'wp_domain_work_home_url_hierarchy_level',
			'value_type' => 'integer',
		],

		/**
		 * Hierarchy level of WordPress installed directory (site_url) for wp-admin
		 *
		 * @access private
		 */
		'site_level' => [
			'key' => 'wp_domain_work_site_url_hierarchy_level',
			'value_type' => 'integer',
		],

		/**
		 * Plugin activation
		 *
		 * @access public
		 */
		'use_domains' => [
			'key' => 'wp_domain_work_domains_dir_activation',
			'value_type' => 'boolean',
		],

		/**
		 * 除外する domain
		 *
		 * @access public
		 */
		'excepted_domains' => [
			'key' => 'wp_domain_work_domains_excepted_domains',
		],

		/**
		 * @access private
		 */
		'domains' => [
			'key' => 'wp_domain_work_registered_domains',
		],

		/**
		 * @access private
		 */
		'class_loaders' => [
			'key' => 'wp_domain_work_domain_class_loaders',
		],

		/**
		 * @access private
		 */
		'functions_files' => [
			'key' => 'wp_domain_work_domain_functions_files',
		],

	];

	/**
	 *
	 */
	private static $private_options = [
		'home_level', 'site_level', 'domains', 'class_loaders', 'functions_files',
	];

	/**
	 */
	protected function __construct() {
		//
	}

	/**
	 *
	 */
	public static function activation() {
		self::installed_level();
	}

	/**
	 *
	 */
	public static function deactivation() {
		self::delete_private_options();
	}

	/**
	 * Get plugin's option key
	 *
	 * @access public
	 *
	 * @param  string $string (optional) if blank return all option keys
	 * @return string
	 */
	public static function get_option_key( $string = '' ) {
		if ( $string ) {
			return array_key_exists( $string, self::$options ) ? self::$options[$string]['key'] : null;
		}
		$return = [];
		foreach ( self::$options as $option ) {
			$return[] = $option['key'];
		}
		return $return;
	}

	/**
	 * Get plugin's option value
	 *
	 * @access public
	 *
	 * @param  string $string
	 * @return string
	 */
	public static function get_option_value( $string ) {
		if ( !$string || !array_key_exists( $string, self::$options ) ) {
			return false;
		}
		return \get_option( self::get_option_key( $string ) );
	}

	/**
	 * Update plugin's option
	 *
	 * @access public
	 *
	 * @param  string $string
	 * @return (bool)
	 */
	public static function update_option( $string, $value ) {
		if ( !$string || !array_key_exists( $string, self::$options ) ) {
			return false;
		}
		return \update_option( self::get_option_key( $string ), $value );
	}

	/**
	 * Plugin init
	 */
	public static function init() {
		$wpdw = self::getInstance();
		self::$error = new \WP_Error();

		if ( is_admin() ) {
			$wpdw -> permalink_structure();

			/**
			 * Settings page in admin menu
			 */
			self::settings_page();
		}

		/**
		 * init services
		 */
		if ( self::get_option_value( 'use_domains' ) && \get_option( 'permalink_structure' ) ) {
			new \service\Domains();
			if ( self::get_option_value( 'home_level' ) !== false
				&& self::get_option_value( 'site_level' ) !== false ) {
				new \service\router();
			}
		}

		/**
		 * Catch error
		 */
		if ( self::$error -> get_error_code() ) {
			add_action( 'admin_notices', [ $wpdw, 'notice' ] );
		}
	}

	/**
	 * Set wordpress core installed level
	 *
	 * @uses wordpress\installed_level
	 */
	private static function installed_level() {
		$levelGetter = new \wordpress\installed_level();
		if ( false === self::get_option_value( 'home_level' ) ) {
			$homeLevel = $levelGetter -> get_level( 'home' );
			self::update_option( 'home_level', $homeLevel );
		}
		if ( false === self::get_option_value( 'site_level' ) ) {
			$siteLevel = $levelGetter -> get_level( 'site' );
			self::update_option( 'site_level', $siteLevel );
		}
	}

	/**
	 * Delete some private options
	 *
	 * @access private
	 */
	private static function delete_private_options() {
		foreach ( self::$private_options as $string ) {
			$key = self::get_option_key( $string );
			if ( $key ) {
				\delete_option( $key );
			}
		}
	}

	/**
	 *
	 */
	private function permalink_structure() {
		if ( !self::get_option_value( 'use_domains' ) ) {
			return;
		}
		$key = 'permalink_structure';
		if ( !\get_option( $key ) ) {
			self::$error -> add(
				$key,
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
	public static function settings_page() {
		/**
		 * Get instance settings page generator
		 */
		$instance = new \wordpress\admin\plugin\settings_page();

		$instance
		-> init( 'wp-domain-work', 'WP Domain Work Settings', 'WP Domain Work' )
			-> section( 'default-setting' )
				-> field( 'plugin-activation' )
				-> option_name( self::get_option_key( 'use_domains' ), 'checkbox', [ 'label' => 'Activate' ] )
				-> description( '' )
		;

		if ( self::get_option_value( 'use_domains' ) ) {
			$instance
			-> init( 'wp-domains', 'Your Domains' )
			;
		}

		$instance -> done();
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
