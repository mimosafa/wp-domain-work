<?php

namespace WP_Domain_Work\WP;

/**
 * 
 */
class roles {
	use \WP_Domain_Work\Utility\Singleton;

	private static $caps = [];

	protected function __construct() {
		$this->init();
	}

	public static function add_cap( Array $cap ) {
		if ( 2 !== count( $cap ) || $cap !== array_values( $cap ) ) {
			return false;
		}
		$_R = self::getInstance();
		$_R::$caps[] = $cap;
	}

	public function init() {
		add_action( 'init', [ $this, 'add_whole_caps' ], 11 );
	}

	public function add_whole_caps() {
		if ( ! self::$caps ) {
			return;
		}
		global $wp_roles;
		$roles = array_keys( $wp_roles->roles );
		foreach ( $roles as $role ) {
			switch ( $role ) {
				case 'administrator' :
				# case 'editor' :
					$_role = get_role( $role );
					foreach ( self::$caps as $array ) {
						$_role->add_cap( 'edit_' . $array[0] );
						$_role->add_cap( 'read_' . $array[0] );
						$_role->add_cap( 'delete_' . $array[0] );
						$_role->add_cap( 'delete_' . $array[1] );
						$_role->add_cap( 'edit_' . $array[1] );
						$_role->add_cap( 'edit_others_' . $array[1] );
						$_role->add_cap( 'delete_others_' . $array[1] );
						$_role->add_cap( 'publish_' . $array[1] );
						$_role->add_cap( 'edit_published_' . $array[1] );
						$_role->add_cap( 'delete_published_' . $array[1] );
						$_role->add_cap( 'delete_private_' . $array[1] );
						$_role->add_cap( 'edit_private_' . $array[1] );
						$_role->add_cap( 'read_private_' . $array[1] );
					}
					break;
				default :
					break 2;
			}
		}
	}

}
