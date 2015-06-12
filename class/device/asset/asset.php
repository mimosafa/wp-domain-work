<?php
namespace WPDW\Device\Asset;

interface asset {
	public static function prepare_arguments( Array &$args, $asset ); // use trait WPDW\Device\Asset\asset_trait
	public function get( $post );
	#public function print_column( $value, $post_id );
}
