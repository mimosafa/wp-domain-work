<?php
namespace WPDW\Device\Asset;

class provision {
	use \WPDW\Util\Array_Function;

	/**
	 * Words that are not allowed to be used as an asset name
	 * @var array
	 */
	private static $_excluded = [
		/**
		 * WordPress reserved words
		 */
		'_edit_last', '_edit_lock', '_wp_old_slug', '_thumbnail_id',
		'_wp_attached_file', '_wp_page_template', '_wp_attachment_metadata',

		/**
		 * Class reserved words (existing property name )
		 * @see WPDW\Device\property
		 */
		'assets', '_assets_data', '_assets_provision',

		// Trimed '_'
		'edit', 'wp', 'thumbnail',
	];

	/**
	 * Cache some type of asset name
	 * @var array
	 */
	private static $meta_assets;
	private static $simple_assets;
	private static $set_assets;

	/**
	 * @var array
	 */
	private static $class_names = [];

	/**
	 * Required & Default arguments on basis of asset
	 */
	private static $_asset_args = [
		'menu_order' => [
			'required' => [ 'type' => 'integer', 'model' => 'post_attribute', 'multiple' => false, 'min' => 0, ],
			'default'  => [ 'label' => 'Order' ]
		],
		'post_parent' => [
			'required' => [ 'type' => 'post', 'model' => 'post_attribute', 'multiple' => false, ],
		],
	];

	/**
	 * Required & Default arguments on basis of type
	 */
	private static $_type_args = [
		'string' => [
			'default' => [ 'model' => 'post_meta', 'multibyte' => true, ]
		],
		'integer' => [
			'default' => [ 'model' => 'post_meta' ]
		],
		'boolean' => [
			'required' => [ 'multiple' => false ],
			'default'  => [ 'model' => 'post_meta', ]
		],
		'datetime' => [
			'required' => [ 'type' => 'datetime', 'unit' => 'datetime_local', ],
			'default'  => [ 'model' => 'post_meta', 'input_format' => 'Y-m-d H:i:s', 'output_format' => 'Y-m-d H:i', ]
		],
		'date' => [
			'required' => [ 'type' => 'datetime', 'unit' => 'date', ],
			'default'  => [ 'model' => 'post_meta', 'input_format' => 'Y-m-d', 'output_format' => 'Y-m-d', ]
		],
		'time' => [
			'required' => [ 'type' => 'datetime', 'unit' => 'time', ],
			'default'  => [ 'model' => 'post_meta', 'input_format' => 'H:i', 'output_format' => 'H:i', ]
		],
		'post_children' => [
			'required' => [ 'type' => 'post', 'model' => 'post', 'context' => 'post_children', ],
			'default'  => [ 'multiple' => true, 'query_args' => [ 'orderby' => 'menu_order', 'order' => 'ASC' ] ]
		],
		'complex' => [
			'required' => [ 'model' => 'structured_post_meta' ],
		],
	];

	/**
	 * Constructor - Initialize static vars
	 *
	 * @access public
	 */
	public function __construct() {
		self::$meta_assets = self::$simple_assets = self::$set_assets = [];
	}

	/**
	 * @access public
	 *
	 * @param  string $type
	 * @return string|boolean
	 */
	public function get_class_name( $name ) {
		if ( ! $name = filter_var( $name ) )
			return false;
		if ( array_key_exists( $name, self::$class_names ) )
			return self::$class_names[$name];
		$class_name = __NAMESPACE__ . '\\type_' . $name;
		if ( class_exists( $class_name ) ) {
			self::$class_names[$name] = $class_name;
			return $class_name;
		}
		return false;
	}

	/**
	 * Sort assets (& assets filter by asset name string)
	 *
	 * @access public
	 *
	 * @param  array &$assets
	 * @return (void)
	 */
	public function sort_assets( &$assets ) {
		$metas  = [];
		$sets   = [];
		$groups = [];
		foreach ( $assets as $asset => $args ) {
			if ( in_array( $asset, self::$_excluded, true ) ) :
				unset( $assets[$asset] );
				continue;
			elseif ( ! preg_match( '/\A[a-z_]?[a-z][a-z0-9_]+\z/', $asset ) ) :
				unset( $assets[$asset] );
				continue;
			elseif ( ! isset( $args['type'] ) ) :
				continue;
			elseif ( $asset[0] === '_' ) :
				$metas[$asset] = $args;
			elseif ( $args['type'] === 'set' ) :
				$sets[$asset] = $args;
			elseif ( $args['type'] === 'group' ) :
				$groups[$asset] = $args;
			else :
				continue;
			endif;
			unset( $assets[$asset] );
		}
		if ( $metas )
			$assets = $metas + $assets;
		if ( $sets )
			$assets = $assets + $sets;
		if ( $groups )
			$assets = $assets + $groups;
	}

	/**
	 * @access public
	 *
	 * @see    WPDW\Device\property::__construct
	 *
	 * @param  array  &$args
	 * @param  string $asset
	 * @param  string $domain
	 * @return (void)
	 */
	public function prepare_assets( &$args, $asset, $domain ) {
		$this->prepare_asset_arguments( $asset, $args );
		if ( ! $args )
			return;

		if ( isset( $args['assets'] ) ) {
			if ( $args['type'] === 'complex' ) {
				/**
				 * Callback function for assets filter of complex type
				 */
				$assetsFilter = function( $asset ) {
					return in_array( $asset, self::$meta_assets, true );
				};
				$args['assets'] = array_filter( $args['assets'], $assetsFilter );
			} else if ( in_array( $args['type'], [ 'set', 'group' ], true ) ) {
				/**
				 * Callback function for assets filter of set/group type
				 */
				$assetsFilter = function( $asset ) use ( $args ) {
					if ( $args['type'] === 'set' ) {
						return in_array( $asset, self::$simple_assets, true );
					} else {
						return in_array( $asset, self::$simple_assets, true ) || in_array( $asset, self::$set_assets, true );
					}
				};
				$args['assets'] = array_filter( $args['assets'], $assetsFilter );
			} else {
				unset( $args['assets'] );
			}
			if ( isset( $args['assets'] ) && $args['assets'] === [] ) {
				$args = null;
				return;
			}
		}

		/**
		 * @uses WPDW\Device\Asset\type_{$type}::prepare_arguments()
		 * @see  Trait: WPDW\Device\Asset\asset_vars::prepare_arguments()
		 */
		$class = $this->get_class_name( $args['type'] );
		$class::prepare_arguments( $args, $asset );

		if ( $asset[0] === '_' ) :
			unset( $args['model'] );
			self::$meta_assets[] = $asset;
		else :
			if ( ! in_array( $args['type'], [ 'set', 'group' ], true ) ) {
				self::$simple_assets[] = $asset;
			} else if ( $args['type'] === 'set' ) {
				self::$set_assets[] = $asset;
			}
			$args['domain'] = $domain;
		endif;
	}

	/**
	 * @access private
	 *
	 * @uses   WPDW\Util\Array_Function::md_merge()
	 *
	 * @param  string $asset
	 * @param  array|mixed &$args
	 * @return (void)
	 */
	private function prepare_asset_arguments( $asset, &$args ) {
		if ( isset( self::$_asset_args[$asset] ) ) {

			$args = is_array( $args ) ? $args : [];
			if ( array_key_exists( 'default', self::$_asset_args[$asset] ) )
				$args = self::md_merge( self::$_asset_args[$asset]['default'], $args );
			if ( array_key_exists( 'required', self::$_asset_args[$asset] ) )
				$args = self::md_merge( $args, self::$_asset_args[$asset]['required'] );

		} else if ( is_array( $args ) && isset( $args['type'] ) ) {

			$type = $args['type'];
			if ( isset( self::$_type_args[$type] ) ) {
				if ( array_key_exists( 'default', self::$_type_args[$type] ) )
					$args = self::md_merge( self::$_type_args[$type]['default'], $args );
				if ( array_key_exists( 'required', self::$_type_args[$type] ) )
					$args = self::md_merge( $args, self::$_type_args[$type]['required'] );
			}
			if ( ! $this->get_class_name( $args['type'] ) ) // Not $type. Important!
				$args = null;

		} else {

			$args = null;

		}
	}

}
