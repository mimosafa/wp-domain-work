<?php
namespace WPDW\Device;

trait status {

	/**
	 * @access public
	 */
	public static function init() {
		static $self;
		if ( ! $self ) {
			if ( func_num_args() ) {
				$arg = func_get_arg( 0 );
				if ( is_array( $arg ) ) {
					if ( ! array_key_exists( 'domain', $arg ) )
						return;
					$domain = $arg['domain'];
				} else if ( is_string( $arg ) && \WPDW\_alias( $arg ) ) {
					$domain = $arg;
				}
				if ( isset( $domain ) )
					$self = new self( $domain );
			}
		}
		return $self;
	}

	protected function __construct( $domain ) {
		_var_dump( get_object_vars( $this ) );
	}

}

class stati {
	use \WPDW\Util\Singleton;

	private static $post_stati = [];

	public static function add_status( $domain, Array $statuses ) {
		//
	}

}
