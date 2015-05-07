<?php
namespace WPDW\Device\Status;

trait built_in {

	/**
	 * Constructor
	 *
	 * @access public
	 *
	 * @param  array $labels
	 * @return (void)
	 */
	public function __construct( Array $labels ) {
		if ( $labels = filter_var_array( $labels, $this->get_filter_definition() ) ) {
			array_walk_recursive( $this->texts, [ $this, 'prepare_texts' ], $labels );
			if ( $this->texts = array_filter( $this->texts ) )
				$this->init();
			if ( property_exists( __CLASS__, 'js_texts' ) ) {
				array_walk( $this->js_texts, [ $this, 'prepare_js_texts' ], $labels );
				if ( $this->js_texts = array_filter( $this->js_texts ) ) {
					add_action( 'admin_footer-post.php', [ $this, 'init_js'], 99 );
					add_action( 'admin_footer-post-new.php', [ $this, 'init_js'], 99 );
				}
			}
		}
	}

	/**
	 * Get filter_var_array definition for labels
	 *
	 * @access public
	 *
	 * @return array
	 */
	public static function get_filter_definition() {
		static $definition;
		if ( ! $definition ) {
			$definition = array_map( function( $var ) {
				return $var === \FILTER_SANITIZE_FULL_SPECIAL_CHARS;
			}, self::$defaults );
		}
		return $definition;
	}

	/**
	 * Get default labels
	 *
	 * @access public
	 *
	 * @param  string $label
	 * @return array
	 */
	public static function get_defaults( $label, $action = null ) {
		if ( ! $label = filter_var( $label ) )
			return;
		$defaults = [];
		foreach ( self::$defaults as $key => $val ) {
			if ( $action && substr( $key, -3 ) === '_on' )
				$defaults[$key] = sprintf( __( $val ), $action );
			else
				$defaults[$key] = sprintf( __( $val ), $label );
		}
		return $defaults;
	}

	/**
	 * Callback method for array_walk_recursive @__construct()
	 *
	 * @access private
	 *
	 * @param  string &$str
	 * @param  string $text
	 * @param  array  $labels
	 * @return (void)
	 */
	private function prepare_texts( &$str, $text, Array $labels ) {
		preg_match( '/\{\{([a-z_]+)\}\}/', $str, $m );
		$key = $m[1];
		if ( isset( $labels[$key] ) ) {
			static $callback;
			if ( ! $callback ) {
				$callback = function( $m ) use ( $labels ) {
					return $labels[$m[1]];
				};
			}
			$str = preg_replace_callback( '/\{\{([a-z_]+)\}\}/', $callback, $str );
		} else {
			$str = null;
		}
	}

	private function prepare_js_texts( &$str, $key, Array $labels ) {
		$str = $labels[$str] ?: null;
	}

	/**
	 * Initialize
	 *
	 * @access private
	 *
	 * @return (void)
	 */
	private function init() {
		if ( is_admin() ) {
			global $pagenow;
			add_action( 'load-' . $pagenow, [ $this, 'set_gettext' ] );
			add_action( 'in_admin_footer',   [ $this, 'reset_gettext' ] );
		}
	}

	public function init_js() {
		$scripts = '';
		foreach ( $this->js_texts as $key => $text ) {
			$js_text = wp_json_encode( $text );
			$scripts .= "\t\t\tpostL10n.{$key} = {$js_text};\n";
		}
		echo <<<EOF
<script type='text/javascript'>
\t\t\twindow.postL10n = window.postL10n || {};
{$scripts}\t\t</script>
EOF;
	}

	/**
	 * Add filters to gettext and gettext_with_context
	 */
	public function set_gettext() {
		add_filter( 'gettext', [ $this, 'gettext' ], 10, 2 );
		add_filter( 'gettext_with_context', [ $this, 'gettext_with_context' ], 10, 3 );
	}

	/**
	 * Remove filters from gettext and gettext_with_context
	 */
	public function reset_gettext() {
		remove_filter( 'gettext', [ $this, 'gettext' ] );
		remove_filter( 'gettext_with_context', [ $this, 'gettext_with_context' ] );
	}

	/**
	 * @param  string $translated
	 * @param  string $text
	 * @return string
	 */
	public function gettext( $translated, $text ) {
		if ( array_key_exists( $text, $this->texts ) )
			$translated = $this->texts[$text][0];
		return $translated;
	}

	/**
	 * @param  string $translated
	 * @param  string $text
	 * @param  string $context
	 * @return string
	 */
	public function gettext_with_context( $translated, $text, $context ) {
		if ( array_key_exists( $text, $this->texts ) && array_key_exists( $context, $this->texts[$text] ) )
			$translated = $this->texts[$text][$context];
		return $translated;
	}

}
