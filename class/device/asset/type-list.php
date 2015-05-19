<?php
namespace WPDW\Device\Asset;

class type_list extends asset_simple {
	use asset_vars;

	protected function filter_value( $value, $post = null ) {}
	public function print_column( $value, $post_id ) {}

}
