<?php

namespace WP_Domain_Work\WP;

class gettext {
	use \singleton;

	private static $texts = [];

	protected function __construct() {
		$this->init();
	}

	private function init() {
		add_filter( 'gettext', [ $this, 'gettext' ], 10, 3 );
	}

	public function gettext( $trancelated_text, $text, $domain ) {
		if ( self::$texts ) {
			foreach ( self::$texts as $array ) {
				$t  = $array[0];
				$tt = $array[1];
				if ( $text === $t ) {
					$trancelated_text = $tt;
				}
			}
		}
		return $trancelated_text;
	}

	public static function set( $text, $trancelated_text, $domain = 'default' ) {
		if ( ! $text || ! is_string( $text ) ) {
			return false;
		}
		if ( ! is_string( $trancelated_text ) ) {
			return;
		}
		$_GT = self::getInstance();
		$_GT::$texts[] = [ $text, $trancelated_text ];
	}

}
