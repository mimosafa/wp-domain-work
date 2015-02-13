<?php

namespace module;

trait status {
	use base;

	private $post_status = [];

	public function __construct() {
		$this->_domain_settings();
		$this->init();
	}

	private function init() {
		//
	}

}
