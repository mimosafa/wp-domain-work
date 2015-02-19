<?php

namespace WP_Domain_Work\Property;

class post_parent {

	public $label = 'Parent';
	public $value;

	private $_post;
	private $_post_type = [];

	public function __construct( $post = 0, $arg = [] ) {
		if ( ! $post = get_post( $post ) ) {
			return null;
		}
		$this->_post = $post;
		if ( get_post_type_object( $this->_post->post_type )->hierarchical ) {
			return;
		}
		if ( ! array_key_exists( 'post_type', $arg ) ) {
			return;
		}
		$this->_post_type = (array) $arg['post_type'];
		if ( array_key_exists( 'label', $arg ) ) {
			$this->label = $arg['label'];
		}
		$this->value = wp_get_post_parent_id( $this->_post ) ?: null;
	}

	public function getArray() {
		return get_object_vars( $this );
	}

	public function getValue() {
		$title = $this->value ? get_the_title( $this->value ) : '';
		return apply_filters( 'wpdw_get_' . $this->domain . '_post_parent_value', $title, $this->value );
	}

}
