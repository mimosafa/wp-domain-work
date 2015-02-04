<?php

namespace wordpress\model;

class post_parent {

	protected $_post;

	public function __construct( $post = 0 ) {
		if ( ! $post = get_post( $post ) ) {
			return null;
		}
		$this->_post = $post;
	}

	public function get() {
		$parent_id = $this->_post->post_parent;
		return ! $parent_id ? false : get_post( $parent_id );
	}

}
