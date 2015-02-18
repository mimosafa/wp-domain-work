<?php

namespace WP_Domain_Work;

/**
 *
 */
class Plugin {
	use Utility\Singleton;

	/**
	 * @var WP_Error
	 */
	private static $error;

	/**
	 * WP Domain Work plugin's options
	 *
	 * @var array
	 */
	private static $option_keys = [
		/**
		 * Hierarchy level of home_url
		 *
		 * @access private
		 */
		'home_level' => 'wp_domain_work_home_url_hierarchy_level',

		/**
		 * Hierarchy level of WordPress installed directory (site_url) for wp-admin
		 *
		 * @access private
		 */
		'site_level' => 'wp_domain_work_site_url_hierarchy_level',

		/**
		 * Plugin activation
		 *
		 * @access public
		 */
		'use_domains' => 'wp_domain_work_domains_dir_activation',

		/**
		 * 除外する domain
		 *
		 * @access public
		 */
		'excepted_domains' => 'wp_domain_work_domains_excepted_domains',

		/**
		 *
		 */
		'domains_dirs' => 'wp_domain_work_domains_directories',

		/**
		 * @access private
		 */
		'domains' => 'wp_domain_work_registered_domains',

		/**
		 * @access private
		 */
		'template_files' => 'wp_domain_work_domain_template_files',

		/**
		 * @access private
		 */
		'functions_files' => 'wp_domain_work_domain_functions_files',

		/**
		 * @access private
		 */
		'post_type_supports' => 'wp_domain_work_post_type_supports',

		/**
		 * This option key is nothing but flag for forcibly scan domain directories in plugin settings page
		 * This option will never save on wp-options table.
		 *
		 * @access public
		 */
		'force_dir_scan' => 'wp_domain_work_force_domain_directories_scan',
	];

	/**
	 */
	protected function __construct() {
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
		$_DW = self::getInstance();
		if ( 'get_' === substr( $name, 0, 4 ) ) {
			array_unshift( $args, substr( $name, 4 ) );
			return call_user_func_array( [ $_DW, 'get_option' ], $args );
		} else if ( 'update_' === substr( $name, 0, 7 ) ) {
			array_unshift( $args, substr( $name, 7 ) );
			return call_user_func_array( [ $_DW, 'update_option' ], $args );
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
		if ( ! array_key_exists( $option, self::$option_keys ) ) {
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
		if ( ! array_key_exists( $option, self::$option_keys ) ) {
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
		if ( ! array_key_exists( $option, self::$option_keys ) ) {
			return false;
		}
		return \delete_option( self::$option_keys[$option] );
	}

	/**
	 * Domains initialize
	 *
	 * @access private
	 *
	 * @uses   WP_Domain_Work\Service\Domains
	 *
	 * @param  boolean $force_scan (optional) if true, domains directories are forcibly scaned.
	 * @return (void)
	 */
	private static function Domains( $force_scan = false ) {
		new \WP_Domain_Work\Service\Domains( $force_scan );
	}

	/**
	 * @access private
	 *
	 * @todo   When un-use_domain, flush rewrite rules does not work well...
	 */
	public function pre_update_option( $value, $option, $old_value ) {
		switch ( $option ) {
			case $this->get_option_key( 'use_domains' ) :
				if ( $value !== $old_value ) {
					if ( ! $value ) {
						self::flush_rewrite_rules(); // does not work well...
					} else {
						self::installed_level();
					}
				}
				break;
			/**
			 * forcibly scan domain directories
			 */
			case $this->get_option_key( 'force_dir_scan' ) :
				if ( $value ) {
					self::Domains( true );
				}
				$value = $old_value; // never saved on wp-options table
				break;
			/**
			 * domains
			 */
			case $this->get_option_key( 'domains' ) :
				if ( $value !== $old_value ) {
					$msg = 'Domains are updated !  ';
					if ( $old_value && is_array( $old_value ) ) {
						foreach ( $value as $domain => $arg ) {
							if ( ! array_key_exists( $domain, $old_value ) ) {
								$msg .= '"' . $domain . '" is added. ';
							} else {
								if ( $arg !== $old_value[$domain] ) {
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
		}
		return $value;
	}

	/**
	 * Plugin init
	 */
	public static function init() {
		$_DW = self::getInstance();
		$_DW::$error = new \WP_Error();
		
		if ( is_admin() ) {
			$_DW->permalink_structure();
			/**
			 * Settings page in admin menu
			 * アドオンプラグインでサブページを追加できるようにするため init にフック
			 */
			add_action( 'init', [ $_DW, 'settings_page' ] );
		}
		/**
		 * init services
		 */
		if ( $_DW->get_option( 'use_domains' ) && \get_option( 'permalink_structure' ) ) {
			self::Domains();
			if ( $_DW->get_option( 'home_level' ) !== false && $_DW->get_option( 'site_level' ) !== false ) {
				new \WP_Domain_Work\Service\Router();
			}
		}
		/**
		 * Catch error
		 */
		if ( self::$error->get_error_code() ) {
			add_action( 'admin_menu', [ $_DW, 'notice' ] );
		}
	}

	/**
	 * Set wordpress core installed level
	 *
	 * @uses wordpress\installed_level
	 */
	private static function installed_level() {
		$_DW = self::getInstance();
		$level = new \WP_Domain_Work\WP\installed_level();

		if ( false === $_DW->get_option( 'home_level' ) ) {
			$homeLevel = $level->get_level( 'home' );
			$_DW->update_option( 'home_level', $homeLevel );
		}
		if ( false === $_DW->get_option( 'site_level' ) ) {
			$siteLevel = $level->get_level( 'site' );
			$_DW->update_option( 'site_level', $siteLevel );
		}
	}

	/**
	 *
	 */
	private function permalink_structure() {
		if ( ! $this->get_option( 'use_domains' ) ) {
			return;
		}
		if ( ! \get_option( 'permalink_structure' ) ) {
			self::$error->add(
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
		$_PAGE = new \WP_Domain_Work\WP\admin\plugin\settings_page();

		$top_page_desc = <<<EOF
This is awesome plugin!
EOF;

		/**
		 * Page: WP Domain Work Settings
		 */
		$_PAGE
		->init( 'wp-domain-work', 'WP Domain Work Settings', 'WP Domain Work' )
			->description( $top_page_desc )
			->section( 'default-setting' )
				->field( 'domains-activation' )
				->option_name( $this->get_option_key( 'use_domains' ), 'checkbox', [ 'label' => 'Use domains' ] )
		;
		if ( ! $this->get_option( 'use_domains' ) ) {
			$_PAGE
					->description( 'ドメインディレクトリーを有効にする場合はチェックを入れてください' )
			;
		} else {
			$_PAGE
					->description( 'ドメインディレクトリーは有効です' )
				->field( 'force-directories-search' )
				->option_name( $this->get_option_key( 'force_dir_scan' ), 'checkbox' )
			;
		}

		/**
		 * Subpage: Your Domains
		 */
		if ( $this->get_option( 'use_domains' ) ) {
			$_PAGE
			->init( 'wp-domains', 'Your Domains' )
				->html( '<pre>' . var_export( $this->get_option( 'domains' ), true ) . '<pre>' )
			;
		}
		/**
		 * アドオンプラグインで管理画面を追加する用
		 */
		$_PAGE = apply_filters( 'wp-domain-work-settings-page', $_PAGE );

		$_PAGE->done();
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
		$codes = self::$error->get_error_codes();
		foreach ( $codes as $code ) {
			$msg  = self::$error->get_error_message( $code );
			$data = self::$error->get_error_data( $code );
			foreach ( $data as $d ) {
				$msg .= ' ' . $d;
			}
			add_settings_error( 'wp-domain-work', $code, $msg, 'error' );
		}
	}

}
