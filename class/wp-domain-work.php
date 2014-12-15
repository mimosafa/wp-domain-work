<?php
/**
 *
 */
class WP_Domain_Work {

	/**
	 *
	 */
	private static $instance = null;

	/**
	 *
	 */
	private static $options = [
		/**
		 *
		 */
		'level' => 'wp_domain_work_wp_core_installed_hierarchy_level_on_server',
	];

	private function __construct() {
		// nothing!
	}

	/**
	 *
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			$cl = __CLASS__;
			self::$instance = new $cl();
		}
		return self::$instance;
	}

	public static function getOptKey( $string = '' ) {
		if ( $string ) {
			return array_key_exists( $string, self::$options ) ? self::$options[$string] : null;
		}
		return self::$options;
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
		$instance = self::get_instance();
		$keys = $instance -> getOptKey();
		foreach ( $keys as $key ) {
			delete_option( $key );
		}
	}

	private function installed_hierarchy_level() {
		$key = self::getOptKey( 'level' );
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

}
