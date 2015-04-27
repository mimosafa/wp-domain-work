<?php
namespace WPDW;

/**
 * @uses WPDW\Util\Singleton
 */
class Options {
	use Util\Singleton;

	/**
	 * WP Domain Work plugin option keys
	 * arias => row
	 *
	 * @var array
	 */
	private $option_keys = [

		// Use 'wp-content/plugins/wp-domain-work/domains' or not
		'sample_domains' => 'wp_domain_work_use_sample_domains_directory',

		// Use 'wp-content/domains' or not
		'root_domains' => 'wp_domain_work_use_root_domains_directory',

		// Use 'wp-content/themes/your-theme/domains' or not
		'theme_domains' => 'wp_domain_work_use_theme_domains_directory',

		// Unuse domains
		'excluded_domains' => 'wp_domain_work_excluded_domains',

		// Domains
		'domains' => 'wp_domain_work_domains',

		// All domains
		'domains_all' => 'wp_domain_work_domains_all',

		// Domains on system
		'domains_alias' => 'wp_domain_work_domains_alias',

		// This option key is nothing but flag for forcibly scan domain directories in plugin settings page
		// This option will never save on wp-options table.
		'force_dir_scan' => 'wp_domain_work_force_domain_directories_scan',

	];

	/**
	 * Option Getter, Updater, Deleter
	 *
	 * @access public
	 */
	public static function __callStatic( $name, $args ) {
		$self = self::getInstance();
		if ( 'get_' === substr( $name, 0, 4 ) ) {
			array_unshift( $args, substr( $name, 4 ) );
			return call_user_func_array( [ $self, 'get' ], $args );
		}
		else if ( 'update_' === substr( $name, 0, 7 ) ) {
			array_unshift( $args, substr( $name, 7 ) );
			return call_user_func_array( [ $self, 'update' ], $args );
		}
		else if ( 'delete_' === substr( $name, 0, 7 ) ) {
			array_unshift( $args, substr( $name, 7 ) );
			return call_user_func_array( [ $self, 'delete' ], $args );
		}
		return null;
	}

	/**
	 * Get row option key, saved in wp-options table
	 *
	 * @access private
	 *
	 * @param  string $option
	 * @return string
	 */
	public static function getKey( $option = null ) {
		$self = self::getInstance();
		if ( ! $option )
			return $self->option_keys;
		return array_key_exists( $option, $self->option_keys ) ? $self->option_keys[$option] : false;
	}

	/**
	 * Get plugin's option value
	 *
	 * @access private
	 *
	 * @param  string $option
	 * @return mixed
	 */
	private function get( $option, $default = false ) {
		if ( ! $value = wp_cache_get( $option, __CLASS__ ) ) {
			if ( ! $option_raw = self::getKey( $option ) )
				return false;
			$value = get_option( $option_raw, $default );
			wp_cache_set( $option, $value, __CLASS__ );
		}
		return $value;
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
	private function update( $option, $newvalue ) {
		if ( $this->get( $option ) === $newvalue )
			return false;
		update_option( self::getKey( $option ), $newvalue );
		wp_cache_delete( $option, __CLASS__ );
		return $this->get( $option );
	}

	/**
	 * Delete plugin's option
	 *
	 * @access private
	 *
	 * @param  string $option
	 * @return boolean
	 */
	private function delete( $option ) {
		if ( $this->get( $option ) === false )
			return false;
		if ( ! delete_option( self::getKey( $option ) ) )
			return false;
		return wp_cache_delete( $option, __CLASS__ );
	}

}
