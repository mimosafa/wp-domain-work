<?php
namespace WPDW\Device\Asset;

class type_complex extends asset_assets {
	use asset_vars;

	/**
	 * @var array
	 */
	protected $assets;

	public function filter_input( $value ) {}
	public function get( $post ) {}
	public function update( $post, $value ) {}
	public function print_column( $value, $post_id ) {}

	public function get_recipe( $post = null ) {
		return parent::get_recipe( $post );
	}

	//

	protected static function arguments_walker( &$arg, $key, $asset ) {
		if ( $key === 'assets' ) :
			/**
			 * @var array $structure
			 */
			self::_validate_structure_array( $arg );
		elseif ( $key === 'admin_form_style' ) :
			/**
			 * @var string $admin_form_style (Fixed: block)
			 */
			$arg = in_array( $arg, [ 'block', 'inline', 'hide' ], true ) ? $arg : 'block';
		else :
			parent::arguments_walker( $arg, $key, $asset );
		endif;
	}

	private static function _validate_structure_array( &$arg ) {
		if ( is_array( $arg ) ) {
			foreach ( $arg as $key => &$array ) {
				if ( ! is_array( $array ) )
					continue;
				\WPDW\Device\property::prepare_asset_arguments( $key, $array );
				if ( ! isset( $array['type'] ) || ! $type = filter_var( $array['type'] ) )
					continue;
				$cl = __NAMESPACE__ . '\\type_' . $type;
				if ( ! class_exists( $cl ) )
					continue;
				array_walk( $array, $cl . '::arguments_walker', $key );
				unset( $array['domain'] );
				unset( $array['model'] );
			}
		}
	}

	protected static function is_met_requirements( Array $args ) {
		return $args['assets'] ? true : false;
	}

}
