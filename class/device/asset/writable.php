<?php
namespace WPDW\Device\Asset;

interface writable {
	public function update( $post, $value );
	public function filter_input( $value );
}
