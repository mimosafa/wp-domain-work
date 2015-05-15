<?php
namespace WPDW\Device\Asset;

interface asset {
	public static function prepare_arguments( Array &$args, $asset );

	public function __construct( Array $args );

	public function get( $post );
	public function update( $post, $value );

	public function output_filter( $var );
	// @todo public function input_filter( $var );

	public function print_column( $value, $post_id );

	public function get_recipe( $post );
}
