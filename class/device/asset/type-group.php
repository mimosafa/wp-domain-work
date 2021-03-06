<?php
namespace WPDW\Device\Asset;

class type_group extends asset_assets implements asset, writable {
	use asset_trait;

	protected static function arguments_walker( &$arg, $key, $asset ) {
		if ( $key === 'admin_form_style' ) :
			/**
			 * @var string $admin_form_style (Fixed: block)
			 */
			$arg = 'block';
		else :
			parent::arguments_walker( $arg, $key, $asset );
		endif;
	}

}
