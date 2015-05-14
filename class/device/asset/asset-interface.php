<?php
namespace WPDW\Device\Asset;

interface asset_interface {

	// private $model;

	/**
	 * @access public
	 *
	 * @see WPDW\Device\property::prepare_assets()
	 *
	 * @param  mixed &$arg
	 * @param  string $key
	 * @param  string $asset
	 * @return (void)
	 */
	public static function arguments_walker( &$arg, $key, $asset );

	public static function arguments_filter( &$args );

	/**
	 * @access public
	 *
	 * @param  mixed $var
	 * @return mixed
	 */
	public function filter( $var );

	/**
	 * @see WPDW\Device\Asset\asset_vars
	 */
	public static function get_defaults();

	/**
	 * @see WPDW\Device\Asset\asset_method
	 */
	public function __construct( Array $args );
	public function get( $post );
	public function update( $post, $value );
	public function get_vars( $post );

	/**
	 * Return value for printing in list table column
	 *
	 * @access public
	 *
	 * - Hooked on '_wpdw_{$name}_column'
	 * @see  WPDW\Device\Admin\posts_column::column_callback()
	 *
	 * @param  mixed $value
	 * @param  int   $post_id
	 * @return string
	 */
	public function print_column( $value, $post_id );

}
