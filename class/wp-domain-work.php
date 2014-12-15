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
		'level' => 'wp_domain_work_core_installed_hierarchy_level_on_server',

		/**
		 *
		 */
		'use_domains' => 'wp_domain_work_domains_dir_activation',

		/**
		 *
		 */
		'domains_dir' => 'wp_domain_work_domains_directory_path',

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
					],
				],
				'theme-debug' => [
					'title' => 'Debug Settings',
					'description' => 'Some debuging option for developers.',
					'fields' => [
						'display-settings-error' => [
							'title' => 'Display Error in Frontend',
							'description' => 'If checked, errors will be displayed in frontend. (Hooked @<code>wp_footer</code>)',
							'callback' => 'checkbox',
							'label' => 'Display',
							'option_name' => 'wp_dct_display_settings_error_in_frontend',
						]
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
			return array_key_exists( $string, self::$options ) ? self::$options[$string] : null;
		}
		return self::$options;
	}

	private static function get_option( $string ) {
		if ( !$string || !array_key_exists( $string, self::$options ) ) {
			return;
		}
		return \get_option( self::get_option_key( $string ) );
	}

	/**
	 * @return bool
	 */
	public static function use_domains() {
		return !!self::get_option( 'use_domains' );
	}

	/**
	 *
	 */
	public static function activation() {
		$instance = self::get_instance();
		$instance -> installed_hierarchy_level();
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
			$instance -> use_domains();
			$instance -> permalink_structure();
			if ( $instance::$error -> get_error_code() ) {
				add_action( 'admin_notices', [ $instance, 'notice' ] );
			}
		}
	}

	/**
	 * Set wordpress core installed level
	 *
	 * @todo  installed hierarchy level in admin
	 */
	private function installed_hierarchy_level() {
		$key = self::get_option_key( 'level' );
		if ( false === get_option( $key ) ) {
			$path = str_replace( $_SERVER['DOCUMENT_ROOT'], '', $_SERVER['SCRIPT_FILENAME'] );
			$array = explode( '/', trim( $path, '/' ) );
			for ( $i = 0; $i < count( $array ); $i++ ) {
				if ( 'index.php' === $array[$i] || 'wp-admin' === $array[$i] ) {
					break;
				}
			}
			update_option( $key, $i );
		}
	}

	/**
	 *
	 */
	private function permalink_structure() {
		if ( !self::get_option( 'use_domains' ) ) {
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
      <b><?= esc_html_e( $msg ) ?></b> <?= esc_html_e( $data[0] ) ?>
    </p>
  </div>
			<?php
		}
	}

	/**
	 * Plugin setting page
	 */
	public static function settings_page() {
		$class = 'wordpress\admin\settings_page';
		if ( class_exists( $class ) ) {
			$pages = new $class();
			//$pages -> position( 62 );
			$pages -> init( self::$pages );
		}
	}

}
