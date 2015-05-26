<?php
namespace WPDW\Device\Asset;

interface asset {
	public static function prepare_arguments( Array &$args, $asset ); // use trait WPDW\Device\Asset\asset_vars
	public function get( $post );
	public function update( $post, $value );
	public function print_column( $value, $post_id );
	public function get_recipe( $post );
	public function filter_input( $value );
}
