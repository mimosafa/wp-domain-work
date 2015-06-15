<?php
namespace WPDW\Device\Asset;

/**
 * Verified arguments class
 *
 * @uses WPDW\Util\Pseudo_Array
 */
class verified extends \WPDW\Util\Pseudo_Array { /* Define only */ }

/**
 * Methods for arguments provision
 */
class provision {
	use \WPDW\Util\Array_Function;

	/**
	 * @var string
	 */
	private $domain;

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
	private $meta_assets   = [];
	private $simple_assets = [];
	private $set_assets    = [];

	/**
	 * @var array
	 */
	private static $class_names = [];

	/**
	 * Required & Default arguments on basis of asset
	 */
	private static $_asset_args = [
		/**
		 * Menu order
		 */
		'menu_order' => [
			'required' => [ 'type' => 'integer', 'model' => 'post_attribute', 'multiple' => false, 'min' => 0, ],
			'default'  => [ 'label' => 'Order' ]
		],

		/**
		 * Post parent
		 */
		'post_parent' => [
			'required' => [ 'type' => 'post', 'model' => 'post_attribute', 'field' => 'ID', 'multiple' => false, ],
		],
	];

	/**
	 * Required & Default arguments on basis of type
	 */
	private static $_type_args = [
		/**
		 * String
		 */
		'string' => [
			'default' => [ 'model' => 'post_meta', 'multibyte' => true, ]
		],

		/**
		 * Integer
		 */
		'integer' => [
			'default' => [ 'model' => 'post_meta' ]
		],

		/**
		 * Boolean
		 */
		'boolean' => [
			'required' => [ 'multiple' => false ],
			'default'  => [ 'model' => 'post_meta', ]
		],

		/**
		 * Datetime
		 */
		'datetime' => [
			'required' => [ 'type' => 'datetime', 'unit' => 'datetime_local', ],
			'default'  => [ 'model' => 'post_meta', 'input_format' => 'Y-m-d H:i:s', 'output_format' => 'Y-m-d H:i', ]
		],

		/**
		 * Date
		 */
		'date' => [
			'required' => [ 'type' => 'datetime', 'unit' => 'date', ],
			'default'  => [ 'model' => 'post_meta', 'input_format' => 'Y-m-d', 'output_format' => 'Y-m-d', ]
		],

		/**
		 * Time
		 */
		'time' => [
			'required' => [ 'type' => 'datetime', 'unit' => 'time', ],
			'default'  => [ 'model' => 'post_meta', 'input_format' => 'H:i', 'output_format' => 'H:i', ]
		],

		/**
		 * List
		 */
		'list' => [
			'default' => [ 'model' => 'post_meta' ]
		],

		/**
		 * Post children
		 */
		'post_children' => [
			'required' => [ 'type' => 'post', 'model' => 'post', 'context' => 'post_children', ],
			'default'  => [ 'multiple' => true, 'query_args' => [ 'orderby' => 'menu_order', 'order' => 'ASC' ] ]
		],

		/**
		 * Complex meta data
		 */
		'complex' => [
			'required' => [ 'model' => 'structured_post_meta' ],
		],
	];

	/**
	 * Constructor - Initialize static vars
	 *
	 * @access public
	 */
	public function __construct( $domain ) {
		if ( ! $domain = filter_var( $domain ) )
			return;
		$this->domain = $domain;
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
			if ( ! self::is_valid_asset_name_string( $asset ) ) :
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
	 * Validate asset name string
	 *
	 * @access public
	 *
	 * @param  string $asset
	 * @return boolean
	 */
	public static function is_valid_asset_name_string( $asset ) {
		if ( ! $name = filter_var( $asset ) )
			return false;
		if ( in_array( $asset, self::$_excluded, true ) )
			return false;
		if ( ! preg_match( '/\A[a-z_]?[a-z][a-z0-9_]+\z/', $asset ) )
			return false;
		return true;
	}

	/**
	 * @access public
	 *
	 * @see    WPDW\Device\property::__construct
	 *
	 * @param  array  &$args
	 * @param  string $asset
	 * @param  string $domain
	 * @return (void) Set arguments as WPDW\Device\Asset\verified object
	 */
	public function prepare_assets( &$args, $asset ) {
		$this->prepare_asset_arguments( $asset, $args );
		if ( ! $args )
			return;

		/**
		 * Validate asset arguments (Before set default arguments)
		 */
		if ( isset( $args['assets'] ) ) {
			if ( $args['type'] === 'complex' ) {
				/**
				 * Callback function for assets filter of complex type
				 */
				$assetsFilter = function( $asset ) {
					return in_array( $asset, $this->meta_assets, true );
				};
				$args['assets'] = array_filter( $args['assets'], $assetsFilter );
			} else if ( in_array( $args['type'], [ 'set', 'group' ], true ) ) {
				/**
				 * Callback function for assets filter of set/group type
				 */
				$assetsFilter = function( $asset ) use ( $args ) {
					if ( $args['type'] === 'set' ) {
						return in_array( $asset, $this->simple_assets, true );
					} else {
						return in_array( $asset, $this->simple_assets, true ) || in_array( $asset, $this->set_assets, true );
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

		if ( ! $args )
			return;

		/**
		 * Validate asset arguments (After set default arguments)
		 */
		if ( $args['type'] === 'complex' ) {
			if ( $args['with_key'] ) {
				if ( $args['key_asset'] && ! in_array( $args['key_asset'], $args['assets'], true ) )
					$args['key_asset'] = '';
				if ( ! $args['key_asset'] )
					$args['with_key'] = false;
			}
		}
		if ( ! $args['multiple'] )
			unset( $args['delimiter'] );

		/**
		 * Add/Remove paramator
		 */
		if ( $asset[0] === '_' ) :
			$this->meta_assets[] = $asset;
		else :
			if ( ! in_array( $args['type'], [ 'set', 'group' ], true ) ) {
				$this->simple_assets[] = $asset;
			} else if ( $args['type'] === 'set' ) {
				$this->set_assets[] = $asset;
			}
			$args['domain'] = $this->domain;
		endif;

		/**
		 * @var  WPDW\Device\Asset\verified
		 * @link __FILE__
		 */
		$args = new verified( $args );
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
		if ( $args && $asset[0] === '_' )
			$args['model'] = 'meta_post_meta';
	}

}
