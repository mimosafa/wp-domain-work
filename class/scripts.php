<?php
namespace WPDW;

class Scripts {
	use Util\Singleton { getInstance as init; }

	/**
	 * @var array
	 */
	private $data = [];

	protected function __construct() {
		$hook = is_admin() ? 'admin_enqueue_scripts' : 'wp_enqueue_scripts';
		add_action( $hook, [ $this, 'register_scripts' ], 9 );
	}

	/**
	 * @access private
	 */
	public function register_scripts() {
		wp_register_script( 'wpdw', \WPDW_PLUGIN_URL . '/js/wp-domain-work.js', [ 'jquery', 'underscore' ], \WPDW_VERSION, true );
		if ( $this->data )
			wp_localize_script( 'wpdw', 'WPDWData', $this->data );
	}

	/**
	 * @access public
	 *
	 * @param  string $key
	 * @param  mixed $data
	 * @return (void)
	 */
	public static function add_data( $key, $data ) {
		$self = self::getInstance();
		if ( $key = filter_var( $key ) )
			$self->data[$key] = $data;
	}

}
