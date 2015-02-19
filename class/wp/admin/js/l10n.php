<?php

namespace WP_Domain_Work\WP\admin\js;

/**
 * L10n data
 *
 * commonL10n
 * - handle: common
 *
 * quicktagsL10n
 * - handle: quicktags
 *
 * wpPointerL10n
 * - handle: wp-pointer
 *
 */
class L10n {
	use \WP_Domain_Work\Utility\Singleton;

	/**
	 * @var array
	 */
	private static $data = [
		/**
		 * @see https://github.com/WordPress/WordPress/blob/4.1-branch/wp-includes/script-loader.php#L441
		 */
		'postL10n' => [],
	];

	/**
	 * @var string
	 */
	private $script_tag_id;

	/**
	 * @var string
	 */
	private static $scripts = '';

	protected function __construct() {
		$this->script_tag_id = strtolower( str_replace( [ '_', '\\' ], '-', __CLASS__ ) );
		$this->init();
	}

	private function init() {
		add_action( 'admin_footer', [ $this, 'init_scripts' ] );
	}

	/**
	 * @access public
	 *
	 * @param  string $context postL10n|..
	 * @param  string $key
	 * @param  string $text
	 * @return bool
	 */
	public static function set( $context, $key, $text ) {
		if ( ! is_string( $key ) || ! $key ) {
			return false;
		}
		$_ = self::getInstance();
		if ( ! array_key_exists( $context, $_::$data ) ) {
			return false;
		}
		$_::$data[$context][$key] = wp_json_encode( $text );
	}

	public function init_scripts() {
		foreach ( self::$data as $context => $texts ) {
			if ( $texts ) {
				self::$scripts .= "  window.{$context} = window.{$context} || {};\n";
				foreach ( $texts as $before => $after ) {
					self::$scripts .= "  {$context}.{$before} = {$after};\n";
				}
			}
		}
		if ( self::$scripts ) {
			# add_action( 'admin_footer-edit.php', [ $this, 'print_script_tag' ], 99 );
			if ( self::$data['postL10n'] ) {
				add_action( 'admin_footer-post.php',     [ $this, 'print_script_tag' ], 99 );
				add_action( 'admin_footer-post-new.php', [ $this, 'print_script_tag' ], 99 );
			}
		}
	}

	public function print_script_tag() {
		$tag_id  = esc_attr( $this->script_tag_id );
		$scripts = self::$scripts;
		echo <<<EOF
<script type='text/javascript' id='{$tag_id}'>
{$scripts}</script>\n
EOF;
	}

}
