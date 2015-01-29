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
			$wpdw -> settings_page();
		}

		/**
		 * init services
		 */
		if ( self::get_option_value( 'use_domains' ) && \get_option( 'permalink_structure' ) ) {
			new \service\Domains();
			if ( self::get_option_value( 'home_level' ) !== false
				&& self::get_option_value( 'site_level' ) !== false ) {
				new \service\Router();
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
	public function settings_page() {
		/**
		 * Get instance settings page generator
		 */
		$instance = new \wordpress\admin\plugin\settings_page();

		$top_page_desc = <<<EOF
This is awesome plugin!
EOF;

		$instance
		-> init( 'wp-domain-work', 'WP Domain Work Settings', 'WP Domain Work' )
			-> description( $top_page_desc )
			-> section( 'default-setting' )
				-> field( 'domains-activation' )
				-> option_name( self::get_option_key( 'use_domains' ), 'checkbox', [ 'label' => 'Use domains' ] )
		;

		if ( !self::get_option_value( 'use_domains' ) ) {
			$instance
				-> description( 'ドメインディレクトリーを有効にする場合はチェックを入れてください' )
			;
		} else {
			$instance
				-> description( 'ドメインディレクトリーは有効です' )
			;
		}

		if ( self::get_option_value( 'use_domains' ) ) {
			$instance
			-> init( 'wp-domains', 'Your Domains' )
				-> html( '<pre>' . var_export( self::get_option_value( 'domains' ), true ) . '<pre>' )
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
