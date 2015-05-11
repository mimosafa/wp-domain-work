<?php
namespace WPDW\Device\Admin;

class postL10n {
	use \WPDW\Util\Singleton;

	/**
	 * @var array
	 */
	private $js_texts = [];

	/**
	 * Constructor
	 *
	 * @access protected
	 */
	protected function __construct() {
		add_action( 'admin_footer-post.php', [ $this, 'print_scripts' ], 99 );
		add_action( 'admin_footer-post-new.php', [ $this, 'print_scripts' ], 99 );
	}

	/**
	 * @access public
	 *
	 * @param  string $key
	 * @param  string $text
	 * @return (void)
	 */
	public static function set( $key, $text ) {
		if ( ! $key = filter_var( $key ) )
			return;
		$self = self::getInstance();
		$self->js_texts[esc_js( $key )] = wp_json_encode( $text );
	}

	/**
	 * @access public
	 */
	public function print_scripts() {
		if ( ! $this->js_texts )
			return;
		$texts = '';
		foreach ( $this->js_texts as $key => $text )
			$texts .= "\tpostL10n.{$key} = {$text};\n";
		echo <<<EOF
<script type='text/javascript'>
\twindow.postL10n = window.postL10n || {};
{$texts}</script>\n
EOF;
	}

}
