<?php

namespace property;

class menu_order {

	public $label = 'Order';
	public $value;

	private $_post;

	private $_increment  = false;
	private $_fill_empty = false;
	private $_add = 'last'; // or 'first'
	private $_unique = false;

	public function __construct( $post = 0, $arg = [] ) {
		if ( ! $post = get_post( $post ) ) {
			return null;
		}
		$this->_post = $post;
		if ( array_key_exists( 'label', $arg ) ) {
			$this->label = $arg['label'];
		}
		$this->value = (int) $this->_post->menu_order;
	}

	public function getArray() {
		return get_object_vars( $this );
	}

}
