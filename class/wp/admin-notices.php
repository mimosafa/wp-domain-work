<?php
namespace WPDW\WP;

/**
 * **CAUTION** This class methods print non-escape text
 */
class admin_notices {
	use \WPDW\Util\Singleton;

	/**
	 * @var array
	 */
	private static $notices = [];

	protected function __construct() {
		add_action( 'admin_notices', [ &$this, 'print_notices' ] );
	}

	private function add( $message, $context ) {
		self::$notices[] = [ 'context' => $context, 'message' => $message ];
	}

	public static function updated( $message ) {
		$self = self::getInstance();
		$self->add( $message, 'updated' );
	}

	public static function error( $message ) {
		$self = self::getInstance();
		$self->add( $message, 'error' );
	}

	public function print_notices() {
		if ( ! self::$notices )
			return;
		foreach ( self::$notices as $notice ) {
			$message = '<strong>' . strtoupper( __( $notice['context'] ) ) . ': </strong> ' . __( $notice['message'] );
			echo <<<EOF
<div class="message {$notice['context']}">
	<p>$message</p>
</div>
EOF;
		}
	}

}
