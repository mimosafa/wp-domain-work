<?php
namespace WPDW\Device\Asset;

interface asset {
	public static function prepare_arguments( Array &$args, $asset ); // use trait WPDW\Device\Asset\asset_trait
	public function get( $post );
	public function update( $post, $value );
	public function filter_input( $value );
	public function print_column( $value, $post_id );
	#public function admin_form_element_dom_array( $value, $namespace );
}
