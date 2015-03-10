<?php

namespace WP_Domain_Work\WP;

class request {
	use \WP_Domain_Work\Utility\Singleton;

	private $query_vars = [];

	private function __construct() {
		$this->init();
	}

	public static function vars( $key, $val ) {
		$self = self::getInstance();
		$self->query_vars[$key] = $val;
	}

	private function init() {
		add_filter( 'request', [ $this, 'request' ] );
	}

	public function request( $vars ) {
		if ( $this->query_vars ) {
			foreach ( $this->query_vars as $key => $val ) {
				if ( array_key_exists( $key, $vars ) ) {
					$vars[$key] = $val;
				}
			}
		}
		return $vars;
	}

}
