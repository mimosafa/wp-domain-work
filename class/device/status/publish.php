<?php
namespace WPDW\Device\Status;

class publish {

	private $texts = [

		'Published' => [
			'{{description}}',
			'post' => '{{state}}',
		],

		'Published on: <b>%1$s</b>' => [
			'{{description}} on: <b>%1$s</b>',
		],

		'Publish <b>immediately</b>' => [
			'{{action}} <b>immediately</b>'
		],

		'Publish on: <b>%1$s</b>' => [
			'{{state}} on: <b>%%1$s</b>'
		],

		'Publish' => [
			'{{action}}'
		],
	];

	public function __construct( Array $labels ) {
		$def = array_fill_keys( [ 'state', 'description', 'action' ], \FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		if ( $labels = filter_var_array( $labels, $def ) ) {
			array_walk_recursive( $this->texts, [ $this, 'prepare_texts' ], $labels );
		}
	}

	private function prepare_texts( &$str, $text, Array $labels ) {
		// preg_replace()?
	}

	private function init() {
		add_action( 'current_screen', [ $this, 'set_gettext' ] );
		add_action( 'admin_footer',   [ $this, 'reset_gettext' ] );
		add_action( 'load-post.php',     [ $this, 'init_postpage' ] );
		add_action( 'load-post-new.php', [ $this, 'init_postpage' ] );
		add_action( 'load-edit.php',     [ $this, 'init_listpage' ] );
	}

	public function set_gettext() {
		add_filter( 'gettext', [ $this, 'gettext' ], 10, 2 );
		add_filter( 'gettext_with_context', [ $this, 'gettext_with_context' ], 10, 3 );
	}

	public function reset_gettext() {
		remove_filter( 'gettext', [ $this, 'gettext' ] );
		remove_filter( 'gettext_with_context', [ $this, 'gettext_with_context' ] );
	}

	public function gettext( $translated, $text ) {
		//
		return $translated;
	}

	public function gettext_with_context( $trabslated, $text, $context ) {
		//
		return $translated;
	}

	public function init_postpage() {
		//
	}

	public function init_listpage() {
		//
	}

}
