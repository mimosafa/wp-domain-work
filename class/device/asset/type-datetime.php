<?php
namespace WPDW\Device\Asset;

class type_datetime implements asset_interface {
	use asset_methods, asset_vars, asset_models;

	protected $model = 'post_meta';

	protected $input_type = 'datetime_local';

	protected $input_format  = 'Y-m-d H:i:s';
	protected $output_format = 'Y-m-d H:i:s';

	protected $min = null;
	protected $max = null;

	/**
	 * @see WPDW\Device\property::prepare_assets()
	 *
	 * @param  mixed  $arg
	 * @param  string $key
	 * @param  string $asset
	 * @return (void)
	 */
	public static function arguments_walker( &$arg, $key, $asset ) {
		if ( $key === 'input_type' && isset( $arg ) ) :
			//

		//

		else :
			// Common
			self::common_arguments( $arg, $key, $asset );
		endif;
	}

}
