<?php
/**
 *
 */
class WP_Domain_Work {

	/**
	 * @var WP_Domain_Work
	 */
	private static $instance = null;

	/**
	 * @var WP_Error
	 */
	private static $error = null;

	/**
	 * @var array
	 */
	private static $options = [

		/**
		 * @access private
		 */
		'home_level' => [
			'key' => 'wp_domain_work_home_url_hierarchy_level',
		],

		/**
		 * @access private
		 */
		'site_level' => [
			'key' => 'wp_domain_work_site_url_hierarchy_level',
		],

		/**
		 * Plugin activation
		 *
		 * @access public
		 */
		'use_domains' => [
			'key' => 'wp_domain_work_domains_dir_activation',
		],

	];

	/**
	 * Singleton
	 */
	private function __construct() {
		// nothing!
	}

	/**
	 * Get instance
	 *
	 * @access public
	 *
	 * @return WP_Domain_Work
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			$cl = __CLASS__;
			self::$instance = new $cl();
		}
		return self::$instance;
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
	private static function get_option_value( $string ) {
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
	private static function update_option( $string, $value ) {
		if ( !$string || !array_key_exists( $string, self::$options ) ) {
			return false;
		}
		return \update_option( self::get_option_key( $string ), $value );
	}

	/**
	 * @return bool
	 */
	public static function use_domains() {
		return !!self::get_option_value( 'use_domains' );
	}

	/**
	 *
	 */
	public static function activation() {
		$instance = self::get_instance();
		$instance -> installed_level();
	}

	/**
	 *
	 */
	public static function deactivation() {
		//
	}

	/**
	 * Plugin init
	 */
	public static function init() {
		if ( is_admin() ) {
			self::$error = new \WP_Error();
			self::permalink_structure();

			/**
			 * Catch error
			 */
			if ( self::$error -> get_error_code() ) {
				$instance = self::get_instance();
				add_action( 'admin_notices', [ $instance, 'notice' ] );
			}

			/**
			 * Settings page in admin menu
			 */
			self::settings_page();
		}
	}

	/**
	 * Set wordpress core installed level
	 *
	 * @uses wordpress\installed_level
	 */
	private function installed_level() {
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
	 *
	 */
	private static function permalink_structure() {
		if ( !self::get_option_value( 'use_domains' ) ) {
			return;
		}
		$key = 'permalink_structure';
		if ( !get_option( $key ) ) {
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
		$instance = new \wordpress\admin\settings_page();

		$instance
		-> init( 'wp-domain-work', 'WP Domain Work Settings', 'Domains' )
			-> section( 'default-setting' )
				-> field( 'plugin-activation' )
				-> option_name( self::get_option_key( 'use_domains' ), 'checkbox', [ 'label' => 'Activate' ] )
				-> description( '' )
		;

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
