<?php
namespace WPDW\Device\Asset;

class type_complex extends asset_assets implements asset, writable {
	use asset_trait;

	/**
	 * @var string
	 */
	protected $model = 'structured_post_meta';

	/**
	 * @var boolean
	 */
	protected $with_key = false;

	/**
	 * @var string
	 */
	protected $key_asset;

	/**
	 * Array_walk callback function
	 *
	 * @see    WPDW\Device\asset_trait::prepare_arguments()
	 *
	 * @access protected
	 *
	 * @param  mixed  &$arg
	 * @param  string $key
	 * @param  string $asset
	 * @return (void)
	 */
	protected static function arguments_walker( &$arg, $key, $asset ) {
		if ( $key === 'model' ) :
			/**
			 * @var string $model
			 */
			$arg = $arg === 'structured_post_meta' ? $arg : null;
		elseif ( $key === 'with_key' ) :
			/**
			 * @var boolean $with_key false
			 */
			$arg = filter_var( $arg, \FILTER_VALIDATE_BOOLEAN );
		elseif ( $key === 'key_asset' ) :
			/**
			 * @var string $key_asset
			 */
			$arg = filter_var( $arg, \FILTER_VALIDATE_REGEXP, [ 'options' => [ 'regexp' => '/\A_[a-z0-9]+/', 'default' => '' ] ] );
		elseif ( $key === 'multiple' ) :
			/**
			 * @var boolean $multiple false
			 */
			$arg = filter_var( $arg, \FILTER_VALIDATE_BOOLEAN );
		elseif ( $key === 'admin_form_style' ) :
			/**
			 * @var string $admin_form_style (Fixed: block)
			 */
			$arg = in_array( $arg, [ 'block', 'inline', 'hide' ], true ) ? $arg : 'block';
		else :
			parent::arguments_walker( $arg, $key, $asset );
		endif;
	}

	protected static function is_met_requirements( Array $args ) {
		return parent::is_met_requirements( $args ) && isset( $args['model'] );
	}

	/**
	 * @access public
	 */
	public function filter_input( $value ) {
		//
	}

	/**
	 * @access protected
	 *
	 * @param  WP_Post $post
	 * @param  array   $args
	 * @return (void)
	 */
	protected function update_assets( \WP_Post $post, Array $values ) {
		//
	}

	public function print_column( $value, $post_id ) {}

}
