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
		 *
		 */
		'home_level' => [
			'key' => 'wp_domain_work_home_url_hierarchy_level',
		],

		/**
		 *
		 */
		'site_level' => [
			'key' => 'wp_domain_work_site_url_hierarchy_level',
		],

		/**
		 *
		 */
		'use_domains' => [
			'key' => 'wp_domain_work_domains_dir_activation',
		],

		/**
		 *
		 */
		'domains_dir' => [
			'key' => 'wp_domain_work_domains_directory_path',
		],

	];

	/**
	 * page structure
	 * 
	 * @var array
	 */
	private static $pages = [
		'wp-dct-settinds' => [
			'page_title' => 'WP Domain Work Plugin Settings',
			'menu_title' => 'WP Domain Work',
			'sections' => [
				'theme-core' => [
					'title' => 'Theme Core Settings',
					'fields' => [
						'domains-activation' => [
							'title' => 'Domains Directory',
							'callback' => 'checkbox',
							'label' => 'Active',
							'option_name' => 'wp_dct_domains_dir_activation',
						],
						'display-settings-error' => [
							'title' => 'Display Error in Frontend',
							'description' => 'If checked, errors will be displayed in frontend. (Hooked @<code>wp_footer</code>)',
							'callback' => 'checkbox',
							'label' => 'Display',
							'option_name' => 'wp_dct_display_settings_error_in_frontend',
						]
					],
				],
				'theme-debug' => [
					'title' => 'Debug Settings',
					'description' => 'Some debuging option for developers.',
					'fields' => [
					],
				],
			],
		],
	];

	private function __construct() {
		// nothing!
	}

	/**
	 * Get instance
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
	 * Get plugin's option keys
	 *
	 * @param  string $string
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

	private static function get_option_value( $string ) {
		if ( !$string || !array_key_exists( $string, self::$options ) ) {
			return false;
		}
		return \get_option( self::get_option_key( $string ) );
	}

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
	 * Plugin initializing
	 */
	public static function init() {
		$instance = self::get_instance();
		if ( is_admin() ) {
			$instance::$error = new \WP_Error();
			$instance -> permalink_structure();
			if ( $instance::$error -> get_error_code() ) {
				add_action( 'admin_notices', [ $instance, 'notice' ] );
			}
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
	private function permalink_structure() {
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

	/**
	 * Plugin setting page
	 *
	 * @uses wordpress\admin\settings_page
	 */
	public static function settings_page() {
		$page = new \wordpress\admin\settings_page();
		$page
		-> page( 'wp-domain-work', 'WP Domain Work Setting' )
		-> menu_title( 'Domains' )
		-> description( 'TEST' )
		-> section( 'plugin-activation' )
		-> description( 'PPPPppluggggggiinn' )
		-> field( 'use-domains', 'checkbox', '', self::get_option_key( 'use_domains' ) )
		-> field( 'aaa-bbb', 'test_field' )
		-> init();
	}

}
