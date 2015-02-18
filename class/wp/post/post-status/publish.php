<?php

namespace WP_Domain_Work\WP\post\post_status;

/**
 * Publish state's texts (if 2nd string is exists, that is context)
 * 
 *
 * << PHP >>
 *
 * 'Published', 'post'
 * - label of status
 * @link https://github.com/WordPress/WordPress/blob/4.1-branch/wp-includes/post.php#L112
 *
 * 'Published <span class="count">(%s)</span>'
 * - label count
 * @link https://github.com/WordPress/WordPress/blob/4.1-branch/wp-includes/post.php#L115
 *
 * 'Published'
 * - description of status (used by get_post_statuses)
 * @link https://github.com/WordPress/WordPress/blob/4.1-branch/wp-includes/post.php#L908
 * - submit meta box for display status & selectable option's label
 * @link https://github.com/WordPress/WordPress/blob/4.1-branch/wp-admin/includes/meta-boxes.php#L76
 * @link https://github.com/WordPress/WordPress/blob/4.1-branch/wp-admin/includes/meta-boxes.php#L98
 *
 * 'Published on: <b>%1$s</b>'
 * - Publish box date format in submit meta box (for publish or private posts)
 * @link https://github.com/WordPress/WordPress/blob/4.1-branch/wp-admin/includes/meta-boxes.php#L170
 *
 * 'Publish <b>immediately</b>'
 * - Publish box date format for draft status post, (no date specified ???)
 * @link https://github.com/WordPress/WordPress/blob/4.1-branch/wp-admin/includes/meta-boxes.php#L172
 * @link https://github.com/WordPress/WordPress/blob/4.1-branch/wp-admin/includes/meta-boxes.php#L180
 *
 * 'Publish on: <b>%1$s</b>'
 * - Publish box date format for draft status post, (date specified ???)
 * @link https://github.com/WordPress/WordPress/blob/4.1-branch/wp-admin/includes/meta-boxes.php#L176
 *
 * 'Publish'
 * - submit button label for publishing action
 * @link https://github.com/WordPress/WordPress/blob/4.1-branch/wp-admin/includes/meta-boxes.php#L253
 * @link https://github.com/WordPress/WordPress/blob/4.1-branch/wp-admin/includes/meta-boxes.php#L254
 * 
 *
 * << JavaScript >>
 * - json data 'postL10n' in admin page
 *
 * 'Published on:'
 * - key: 'publishOnPast'
 * @link https://github.com/WordPress/WordPress/blob/4.1-branch/wp-includes/script-loader.php#L446
 *
 * 'Publish'
 * - key: 'publish'
 * @link https://github.com/WordPress/WordPress/blob/4.1-branch/wp-includes/script-loader.php#L451
 * 
 * 'Published'
 * - key: 'published'
 * @link https://github.com/WordPress/WordPress/blob/4.1-branch/wp-includes/script-loader.php#L461
 */
class publish {

	private $labels = [
		'label'       => '',
		'description' => '',
		'save_action' => '',
	];

	private $texts = [];

	private $texts_with_contexts = [];

	private static $defaults = [
		'label'       => [ 'Published', 'post' ],
		'description' => 'Published',
		'save_action' => 'Publish',
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
		$statusObj = $wp_post_statuses['publish'];
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

	protected function js_texts() {
		\WP_Domain_Work\WP\admin\js\L10n::set( 'postL10n', 'published', $this->labels['description'] );
	}

}
