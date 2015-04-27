<?php
namespace WPDW\Device\Asset;

interface asset_interface {

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
	public function get_vars( $post );

	/**
	 * @see WPDW\Device\Asset\asset_model
	 */
	public function get( $post );
	public function update( $post, $value );
}
