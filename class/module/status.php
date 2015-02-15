<?php

namespace module;

trait status {
	use base;

	private $post_status = [];

	public function __construct() {
		$this->_domain_settings();
		$this->init();
	}

	private function init() {
		global $wp_post_statuses;
		if ( property_exists( $this, 'statuses' ) && is_array( $this->statuses ) && $this->statuses ) {
			foreach ( $this->statuses as $status => $arg ) {
				if ( array_key_exists( $status, $wp_post_statuses ) ) {
					//
				}
				/*
				if ( array_key_exists( $status, self::$builtin ) && is_string( $arg ) && $arg ) {
					$this->gettext( self::$builtin[$status], $arg );
				}
				*/
			}
		}
	}

	private function gettext( $text, $trancelated_text ) {
		\wordpress\gettext::set( $text, $trancelated_text );
	}

}
