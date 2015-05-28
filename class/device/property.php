<?php
namespace WPDW\Device;

/**
 * @uses WPDW\Util\Singleton
 * @uses WPDW\Device\Module\Functions
 */
trait property {
	use \WPDW\Util\Singleton, \WPDW\Util\Array_Function, Module\Methods;

	/**
	 * Asset data
	 * @var array
	 */
	private $_data = [];

	/**
	 * Constructor
	 *
	 * @access protected
	 */
	protected function __construct() {
		if ( $this->isDefined( 'assets' ) ) {
			$this->sort_assets();
			array_walk( $this->assets, [ &$this, 'prepare_assets' ] );
			$this->assets = array_filter( $this->assets );
		}
		#_var_dump( $this );
	}

	/**
	 * (Nothing but) Sort assets
	 */
	private function sort_assets() {
		$metas = [];
		$sets  = [];
		$groups = [];
		foreach ( $this->assets as $asset => $args ) {
			if ( ! isset( $args['type'] ) )
				continue;
			if ( $asset[0] === '_' )
				$metas[$asset] = $args;
			if ( $args['type'] === 'set' )
				$sets[$asset] = $args;
			if ( $args['type'] === 'group' )
				$groups[$asset] = $args;
			else
				continue;
			unset( $this->assets[$asset] );
		}
		if ( $metas )
			$this->assets = $metas + $this->assets;
		if ( $sets )
			$this->assets = $this->assets + $sets;
		if ( $groups )
			$this->assets = $this->assets + $groups;
	}

	/**
	 * @access private
	 *
	 * @param  array  &$args
	 * @param  string $asset
	 */
	private function prepare_assets( &$args, $asset ) {
		/**
		 * Words that are not allowed to be used as an asset name
		 * @var array
		 */
		static $excluded = [
			// WordPress reserved words
			'_edit_last', '_edit_lock', '_wp_old_slug', '_thumbnail_id',
			'_wp_attached_file', '_wp_page_template', '_wp_attachment_metadata',

			'edit', 'wp', 'thumbnail',
			
			// Class reserved words (existing property name )
			'assets', '_data',

			'data',
		];
		static $metaAssets   = [];
		static $simpleAssets = [];
		static $setAssets    = [];

		if ( in_array( $asset, $excluded, true ) ) :
			$args = null;
			return;
		elseif ( preg_match( '/[^a-z0-9_]/', $asset ) ) :
			$args = null;
			return;
		else :
			self::prepare_asset_arguments( $asset, $args );
			if ( ! isset( $args['type'] ) ) {
				$args = null;
				return;
			}
		endif;

		if ( isset( $args['assets'] ) ) {
			if ( $args['type'] === 'complex' ) {
				/**
				 * Callback function for assets filter of complex type
				 */
				$assetsFilter = function( $asset ) use ( $metaAssets ) {
					return in_array( $asset, $metaAssets, true );
				};
				$args['assets'] = array_filter( $args['assets'], $assetsFilter );
			} else if ( in_array( $args['type'], [ 'set', 'group' ], true ) ) {
				/**
				 * Callback function for assets filter of set/group type
				 */
				$assetsFilter = function( $asset ) use ( $args, $simpleAssets, $setAssets ) {
					if ( $args['type'] === 'set' ) {
						return in_array( $asset, $simpleAssets, true );
					} else {
						return in_array( $asset, $simpleAssets, true ) || in_array( $asset, $setAssets, true );
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

		if ( ! $this->get_class_name( $args['type'] ) ) {
			$args = null;
			return;
		}

		/**
		 * @uses WPDW\Device\Asset\type_{$type}::prepare_arguments()
		 * @see  Trait: WPDW\Device\Asset\asset_vars::prepare_arguments()
		 */
		$class = $this->get_class_name( $args['type'] );
		$class::prepare_arguments( $args, $asset );

		if ( $asset[0] === '_' )
			$metaAssets[] = $asset;
		else if ( ! in_array( $args['type'], [ 'set', 'group' ], true ) )
			$simpleAssets[] = $asset;
		else if ( $args['type'] === 'set' )
			$setAssets[] = $asset;
	}

	/**
	 * @access public
	 *
	 * @uses   WPDW\Util\Array_Function::md_merge()
	 *
	 * @param  string $asset
	 * @param  array|mixed &$args
	 */
	public static function prepare_asset_arguments( $asset, &$args ) {
		/**
		 * Required & Default arguments on basis of asset
		 */
		static $_asset_args = [
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
		static $_type_args = [
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

		if ( array_key_exists( $asset, $_asset_args ) ) {

			$args = is_array( $args ) ? $args : [];
			if ( array_key_exists( 'default', $_asset_args[$asset] ) )
				$args = self::md_merge( $_asset_args[$asset]['default'], $args );
			if ( array_key_exists( 'required', $_asset_args[$asset] ) )
				$args = self::md_merge( $args, $_asset_args[$asset]['required'] );

		} else if ( is_array( $args ) && isset( $args['type'] ) ) {

			$type = $args['type'];
			if ( array_key_exists( $type, $_type_args ) ) {
				if ( array_key_exists( 'default', $_type_args[$type] ) )
					$args = self::md_merge( $_type_args[$type]['default'], $args );
				if ( array_key_exists( 'required', $_type_args[$type] ) )
					$args = self::md_merge( $args, $_type_args[$type]['required'] );
			}

		} else {

			$args = null;

		}

		if ( $asset[0] === '_' )
			unset( $args['model'] );
		if ( $args )
			$args['domain'] = explode( '\\', __CLASS__ )[1];
	}

	/**
	 * @access private
	 *
	 * @param  string $type
	 * @return string|boolean
	 */
	private function get_class_name( $name ) {
		static $class_names = [];
		if ( ! $name = filter_var( $name ) )
			return false;
		if ( array_key_exists( $name, $class_names ) )
			return $class_names[$name];
		$class_name = __NAMESPACE__ . '\\Asset\\type_' . $name;
		if ( class_exists( $class_name ) ) {
			$class_names[$name] = $class_name;
			return $class_name;
		}
		return false;
	}

	/**
	 * Get assets setting
	 *
	 * @access public
	 *
	 * @uses   WPDW\Device\Module\Functions::isDefined()
	 *
	 * @param  string $name (optional) if blank, get all settings
	 * @return array|null
	 */
	public function get_setting( $name = '' ) {
		if ( ! $this->isDefined( 'assets' ) )
			return;
		if ( ! $name )
			return $this->assets;
		return isset( $this->assets[$name] ) ? $this->assets[$name] : null;
	}

	/**
	 * @access public
	 *
	 * @param  string $name
	 * @return boolean
	 */
	public function __isset( $name ) {
		if ( array_key_exists( $name, $this->_data ) )
			return true;
		if ( $setting = $this->get_setting( $name ) ) {
			$class = $this->get_class_name( $setting['type'] );
			$this->_data[$name] = new $class( $setting );
			return true;
		}
		return false;
	}

	/**
	 * @access public
	 *
	 * @param  string $name
	 * @return WPDW\Device\Asset\{$type}
	 */
	public function __get( $name ) {
		return isset( $this->$name ) ? $this->_data[$name] : null;
	}

}
