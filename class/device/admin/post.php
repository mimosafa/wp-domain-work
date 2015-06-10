<?php
namespace WPDW\Device\Admin;

/**
 * Inherited by
 * - WPDW\Device\Admin\meta_boxes
 * - WPDW\Device\Admin\edit_form_advamced
 */
abstract class post {

	const FORM_ID_PREFIX = 'wpdw-form-';

	/**
	 * @var  WP_Domain\{$domain}\property
	 */
	protected static $property;

	/**
	 * @var WPDW\WP\nonce
	 */
	protected static $nonce;

	protected static $forms = [];

	/**
	 * @var array
	 */
	protected static $done_assets = [];

	/**
	 * Constructor
	 *
	 * @access public
	 *
	 * @uses   WPDW\_property()
	 * @see    wp-domain-work/inc/functions.php
	 *
	 * @param  string $domain
	 */
	public function __construct( $domain ) {
		if ( ! self::$property )
			self::$property = \WPDW\_property( $domain );

		/**
		 * Nonce gen
		 * - $domain must be the same as when saving
		 * @see WPDW\Device\Admin\save_post::__construct()
		 */
		if ( ! self::$nonce )
			self::$nonce = \WPDW\WP\nonce::getInstance( $domain );

		/**
		 * Add hook only once
		 */
		static $done = false;
		if ( ! $done ) {
			add_filter( 'is_protected_meta', [ &$this, 'is_protected_meta' ], 10, 3 );
			$done = true;
		}
	}

	/**
	 * @access protected
	 *
	 * @uses WPDW\Util\Array_Function::flatten()
	 *
	 * @param  array $args
	 * @return array
	 */
	protected function prepare_arguments( Array &$args ) {
		array_walk( $args, [ &$this, 'arguments_walker' ] );
		if ( ! isset( $args['asset'] ) || ! $args['asset'] )
			return;
		$args['id'] = implode( '_', (array) $args['asset'] );
		if ( ! isset( $args['title'] ) || ! $args['title'] ) {
			$map = function( $asset ) {
				if ( ! $setting = self::$property->get_setting( $asset ) )
					return null;
				return $setting['label'];
			};
			$titles = array_filter( array_map( $map, (array) $args['asset'] ) );
			$args['title'] = implode( ' / ', $titles );
		}
	}

	/**
	 * @access protected
	 *
	 * @param  mixed  &$arg
	 * @param  string $key
	 * @return (void)
	 */
	protected function arguments_walker( &$arg, $key ) {
		static $assetFilter;
		if ( ! $assetFilter ) {
			$assetFilter = function( $var ) use ( &$assetFilter ) {
				if ( in_array( $var, self::$done_assets, true ) )
					return null;
				if ( ! $setting = self::$property->get_setting( $var ) )
					return null;
				if ( $var[0] !== '_' )
					self::$done_assets[] = $var;
				// Recursive
				if ( isset( $setting['assets'] ) && $setting['type'] !== 'complex' ) {
					foreach ( $setting['assets'] as $asset )
						$assetFilter( $asset );
				}
				return $var;
			};
		}

		if ( in_array( $key, [ 'title', 'description' ], true ) ) :
			$arg = filter_var( $arg, \FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		elseif ( $key === 'asset' ) :
			$arg = filter_var( $arg, \FILTER_CALLBACK, [ 'options' => $assetFilter ] );
			if ( is_array( $arg ) ) {
				$arg = \WPDW\Util\Array_Function::flatten( $arg, true );
			}
		endif;
	}

	/**
	 * (Magic method) print_fieldset_*asset_name*(): Admin forms callback
	 *
	 * @access public
	 *
	 * @param  callable $func
	 * @param  array $arg
	 * @return (void)
	 */
	public function __call( $func, $arg ) {
		if ( ! preg_match( '/\Arender_([a-z][a-z0-9_]+)/', $func, $m ) )
			return;

		/**
		 * @var WP_Post
		 */
		$post = $arg[0];
		if ( ! is_object( $post ) && get_class( $post ) !== 'WP_Post' )
			return;

		$args = self::$forms[$m[1]];
		$asset = $args['asset'];

		if ( is_array( $asset ) ) {
			$this->output_form_table( $asset, $post );
			return;
		}

		$this->output_asset_form( $asset, $post );
		$this->output_nonce( $asset );
	}

	/**
	 * Render form element
	 *
	 * @access protected
	 *
	 * @uses   WPDW\Asset\type_{$type}::admin_form_element()
	 *
	 * @param  string  $asset
	 * @param  WP_Post $post
	 * @return (void)
	 */
	protected function output_asset_form( $asset, \WP_Post $post ) {
		$html = self::$property->$asset->admin_form_element( $post );
		#echo '<pre>';
		echo html_entity_decode( $html );
		#echo '</pre>';
	}

	/**
	 * Render form table element
	 *
	 * @access private
	 *
	 * @param  array   $assets
	 * @param  WP_Post $post
	 * @return (void)
	 */
	private function output_form_table( Array $assets, \WP_Post $post ) {
		echo '<table class="form-table"><tbody>';
		foreach ( $assets as $asset ) {
			echo '<tr><th><label for="' . self::FORM_ID_PREFIX . esc_attr( $asset ) . '">';
			esc_html_e( self::$property->get_setting( $asset )['label'] );
			echo '</label></th><td>';
			$this->output_asset_form( $asset, $post );
			$this->output_nonce( $asset );
			echo '</td></tr>';
		}
		echo '</tbody></table>';
	}

	/**
	 * Render nonce hidden form
	 *
	 * @access private
	 *
	 * @param  string $asset
	 * @return (void)
	 */
	private function output_nonce( $asset ) {
		$name  = self::$nonce->get_nonce( $asset );
		$value = self::$nonce->create_nonce( $asset );
		printf(
			"<input type=\"hidden\" name=\"%1\$s\" id=\"%1\$s\" value=\"%2\$s\" />",
			esc_attr( $name ), esc_attr( $value )
		);
	}

	/**
	 * @access public
	 *
	 * @see    https://github.com/mimosafa/WordPress/blob/4.1-branch/wp-admin/includes/meta-boxes.php#L573
	 * @see    https://github.com/mimosafa/WordPress/blob/4.1-branch/wp-admin/includes/template.php#L560
	 * @see    https://github.com/mimosafa/WordPress/blob/4.1-branch/wp-admin/includes/template.php#L579
	 * @see    https://github.com/mimosafa/WordPress/blob/4.1-branch/wp-includes/meta.php#L1565
	 *
	 * @param  boolean $protected
	 * @param  string  $meta_key
	 * @param  string  $meta_type
	 * @return boolean
	 */
	public function is_protected_meta( $protected, $meta_key, $meta_type ) {
		if ( self::$done_assets && $meta_type === 'post' ) {
			if ( in_array( $meta_key, self::$done_assets, true ) )
				$protected = true;
		}
		return $protected;
	}

}
