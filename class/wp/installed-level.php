<?php

namespace WP_Domain_Work\WP;

/**
 * Define WordPress installed level from http root.
 *
 * This class has 2 specific properties.
 * - $home_url_hierarchy is url path depth in front-end defined by value of WP_HOME.
 * - $site_url_hierarchy is url path depth in admin page defined by value of WP_SITEURL.
 *
 * Each property will be returned only by getter function get_level().
 */
class installed_level {

	private $http_host;
	private $home_url_hierarchy;
	private $site_url_hierarchy;

	public function __construct() {
		if ( !defined( 'WP_HOME' ) || !defined( 'WP_SITEURL' ) ) {
			return;
		}
		$this -> http_host = $_SERVER['HTTP_HOST'];
		$this -> init();
	}

	private function init() {
		$this -> home_url();
		$this -> site_url();
	}

	private function home_url() {
		$array = explode( $this -> http_host, WP_HOME );
		$paths = array_filter( explode( '/', trim( $array[1], '/' ) ) );
		$this -> home_url_hierarchy = count( $paths );
	}

	private function site_url() {
		$array = explode( $this -> http_host, WP_SITEURL );
		$paths = array_filter( explode( '/', trim( $array[1], '/' ) ) );
		$this -> site_url_hierarchy = count( $paths );
	}

	/**
	 * Get hierarchy level
	 *
	 * @access public
	 *
	 * @param  string $var 'home'|'site'
	 * @return integer
	 */
	public function get_level( $var ) {
		if ( $var === 'home' ) {
			return $this -> home_url_hierarchy;
		} else if ( $var === 'site' ) {
			return $this -> site_url_hierarchy;
		}
	}

}
