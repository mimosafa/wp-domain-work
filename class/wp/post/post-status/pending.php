<?php

namespace WP_Domain_Work\WP\post\post_status;

/**
 * Pending state's texts (if 2nd string is exists, that is context)
 * 
 *
 * << PHP >>
 *
 * 'Pending', 'post'
 * - label of status
 * @link https://github.com/WordPress/WordPress/blob/4.1-branch/wp-includes/post.php#L133
 *
 * 'Pending <span class="count">(%s)</span>'
 * - label count
 * @link https://github.com/WordPress/WordPress/blob/4.1-branch/wp-includes/post.php#L136
 *
 * 'Pending Review'
 * - description of status (used by get_post_statuses)
 * @link https://github.com/WordPress/WordPress/blob/4.1-branch/wp-includes/post.php#L906
 * - submit meta box for display status & selectable option's label
 * @link https://github.com/WordPress/WordPress/blob/4.1-branch/wp-admin/includes/meta-boxes.php#L82
 * @link https://github.com/WordPress/WordPress/blob/4.1-branch/wp-admin/includes/meta-boxes.php#L104
 *
 * 'Save as Pending'
 * - submit meta box for saving as pending
 * @link https://github.com/WordPress/WordPress/blob/4.1-branch/wp-admin/includes/meta-boxes.php#L33
 *
 * 'Submit for Review' ################# yet #################
 * - submit button label for no capability to publish post
 * @link https://github.com/WordPress/WordPress/blob/4.1-branch/wp-admin/includes/meta-boxes.php#L257
 * @link https://github.com/WordPress/WordPress/blob/4.1-branch/wp-admin/includes/meta-boxes.php#L258
 * 
 * 'Pending', 'post state'
 * - in posts list table (@edit.php) pending status post's sufix (used by _post_states)
 * @link https://github.com/WordPress/WordPress/blob/4.1-branch/wp-admin/includes/template.php#L1648
 * 
 *
 * << JavaScript >>
 * - json data 'postL10n' in admin page
 *
 * 'Save as Pending'
 * - key: 'savePending'
 * @link https://github.com/WordPress/WordPress/blob/4.1-branch/wp-includes/script-loader.php#L454
 */
class pending {

	private $labels = [
		'label'       => '',
		'description' => '',
		'save_action' => '',
		'states'      => '',
	];

	private $texts = [];

	private $texts_with_contexts = [];

	private static $defaults = [
		'label'       => [ 'Pending', 'post' ],
		'description' => 'Pending Review',
		'save_action' => 'Save as Pending',
		'states'      => [ 'Pending', 'post state' ],
	];

	public function __construct( Array $args ) {
		if ( ! $this->set_texts( $args ) ) {
			return;
		}
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
		$statusObj = $wp_post_statuses['pending'];
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
		\WP_Domain_Work\WP\admin\js\L10n::set( 'postL10n', 'savePending', $this->labels['save_action'] );
	}

}
