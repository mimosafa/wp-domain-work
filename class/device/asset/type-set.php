<?php
namespace WPDW\Device\Asset;

class type_set extends asset_assets {
	use asset_vars;

	protected static function arguments_walker( &$arg, $key, $asset ) {
		if ( $key === 'admin_form_style' ) :
			/**
			 * @var string $admin_form_style (Fixed: block)
			 */
			$arg = in_array( $arg, [ 'block', 'inline', 'hide' ], true ) ? $arg : 'block';
		else :
			parent::arguments_walker( $arg, $key, $asset );
		endif;
	}

}
