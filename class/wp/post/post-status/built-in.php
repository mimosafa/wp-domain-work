<?php

namespace WP_Domain_Work\WP\post\post_status;

trait built_in {
	use \WP_Domain_Work\Utility\classname;

	protected $status;

	protected $texts = [];

	protected $texts_with_contexts = [];

	// protected $labels = [];
	// protected static $defaults = [];

	public function __construct( Array $args ) {
		if ( ! $this->set_texts( $args ) ) {
			return;
		}
		$this->status = self::getClassName( $this );
		$this->init();
	}

	protected function set_texts( Array $args ) {
		if ( ! array_key_exists( 'label', $args ) || ! is_string( $args['label'] ) || ! $args['label'] ) {
			return false;
		}
		array_walk( $this->labels, function( &$string, $key, $args ) {
			if ( array_key_exists( $key, $args ) && is_string( $args[$key] ) && $args[$key] ) {
				$string = $args[$key];
			} else {
				$string = $args['label'];
			}
		}, $args );
		foreach ( self::$defaults as $key => $string ) {
			if ( is_array( $string ) ) {
				$text_context = implode( '__', $string );
				$this->texts_with_contexts[$text_context] = $this->labels[$key];
			} else {
				$this->texts[$string] = $this->labels[$key];
			}
		}
		return true;
	}

	protected function init() {
		$this->register_post_status();
		if ( is_admin() ) {
			$this->gettext();
			$this->gettext_with_context();
			$this->js_texts();
		}
	}

	protected function register_post_status() {
		global $wp_post_statuses;
		$statusObj = $wp_post_statuses[$this->status];
		$statusObj->label = $this->labels['label'];
		if ( is_admin() ) {
			$label_count_string = sprintf( '%s <span class="count">(%%s)</span>', $this->labels['label'] );
			$statusObj->label_count = _n_noop( $label_count_string, $label_count_string );
		}
	}

	protected function gettext() {
		add_filter( 'gettext', function( $translated, $text ) {
			if ( array_key_exists( $text, $this->texts ) ) {
				$translated = $this->texts[$text];
			}
			return $translated;
		}, 10, 2 );
	}

	protected function gettext_with_context() {
		add_filter( 'gettext_with_context', function( $translated, $text, $context ) {
			$text_context = $text . '__' . $context;
			if ( array_key_exists( $text_context, $this->texts_with_contexts ) ) {
				$translated = $this->texts_with_contexts[$text_context];
			}
			return $translated;
		}, 10, 3 );
	}

	/**
	 * gettext へのフィルター処理が、postL10n のローカライズ('wp_default_scripts')に間に合わないため、やむを得ず js で上書き
	 * @see https://github.com/WordPress/WordPress/blob/4.1-branch/wp-includes/script-loader.php#L1041
	 */
	protected function js_texts() {
		//
	}

}
