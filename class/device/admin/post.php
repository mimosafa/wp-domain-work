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
		if ( ! $domain = filter_var( $domain ) )
			return;

		if ( ! self::$property )
			self::$property = \WPDW\_property( $domain );

		if ( ! self::$nonce )
			self::$nonce = \WPDW\WP\nonce::getInstance( $domain );

		static $done = false;
		if ( ! $done ) {
			add_action( 'admin_enqueue_scripts', [ &$this, 'localize_assets_data' ], 8 );
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
		$post = $arg[0]; // WP_Post
		$key  = $m[1];
		$args = self::$forms[$key];
		$asset = $args['asset'];

		//
		echo '<pre>'; var_dump( $args ); echo '</pre>';
	}

	/**
	 * @access public
	 *
	 * @uses   WPDW\Scripts::add_data()
	 *
	 * @return (void)
	 */
	public function localize_assets_data() {
		if ( self::$forms ) {
			$data = [];
			$walker = function( $asset ) use ( &$data ) {
				$nonce = [
					'name'  => self::$nonce->get_nonce( $asset ),
					'value' => self::$nonce->create_nonce( $asset ),
					'refer' => wp_unslash( $_SERVER['REQUEST_URI'] )
				];
				$data[$asset] = array_merge(
					self::$property->$asset->get_recipe(),
					[ 'nonce' => $nonce ]
				);
			};
			$assets = [];
			foreach ( self::$forms as $form ) {
				$assets[] = $form['asset'];
			}
			array_walk_recursive( $assets, $walker );

			if ( $data )
				\WPDW\Scripts::add_data( 'assets', $data );
			/*
			$data = [];
			foreach ( self::$done_assets as $asset ) {
				$nonce = [
					'name'  => self::$nonce->get_nonce( $asset ),
					'value' => self::$nonce->create_nonce( $asset ),
					'refer' => wp_unslash( $_SERVER['REQUEST_URI'] )
				];
				$data[$asset] = array_merge(
					self::$property->$asset->get_recipe(),
					[ 'nonce' => $nonce ]
				);
			}
			\WPDW\Scripts::add_data( 'assets', $data );
			\WPDW\Scripts::add_data( 'form_id_prefix', self::FORM_ID_PREFIX );
			*/
		}
	}

	//

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
