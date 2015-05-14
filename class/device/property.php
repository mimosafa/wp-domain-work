<?php
namespace WPDW\Device;

/**
 * @uses WPDW\Util\Singleton
 * @uses WPDW\Device\Module\Functions
 */
trait property {
	use \WPDW\Util\Singleton, Module\Methods;

	/**
	 * Asset data
	 * @var array
	 */
	private $_data = [];

	/**
	 * Words that are not allowed to be used as an asset name
	 * @var array
	 */
	private static $_excluded = [
		// WordPress reserved words
		'_edit_last', '_edit_lock', '_wp_old_slug', '_thumbnail_id',
		'_wp_attached_file', '_wp_page_template', '_wp_attachment_metadata',
		
		// Class reserved words (existing property name )
		'assets', '_data', '_excluded', '_required', '_default',
	];

	/**
	 * Required arguments
	 * @var array
	 */
	private static $_required = [
		// On basis of asset name
		'asset' => [
			'menu_order'  => [ 'type' => 'integer', 'model' => 'post_attribute', 'multiple' => false, 'min' => 0, ],
			'post_parent' => [ 'type' => 'post',    'model' => 'post_attribute', 'multiple' => false, ],
		],

		// On basis of type
		'type' => [
			'post_children' => [ 'type' => 'post', 'model' => 'post_children', ],
			'time' => [ 'type' => 'datetime', 'input_type' => 'time', ],
			'boolean' => [ 'multiple' => false, ]
		],
	];

	/**
	 * Default arguments
	 * @var array
	 */
	private static $_default = [
		// On basis of asset name
		'asset' => [
			'menu_order' => [ 'label' => 'Order' ],
		],

		// On basis of type
		'type' => [
			'post_children' => [ 'multiple' => true, ],
			'time' => [ 'input_format' => 'H:i', 'output_format' => 'H:i', ]
		],
	];

	/**
	 * Constructor
	 *
	 * @access protected
	 */
	protected function __construct() {
		if ( $this->isDefined( 'assets' ) ) {
			array_walk( $this->assets, [ &$this, 'prepare_assets' ] );
			$this->assets = array_filter( $this->assets );
		}
		_var_dump( $this );
	}

	/**
	 * @access private
	 *
	 * @param  array  &$args
	 * @param  string $asset
	 */
	private function prepare_assets( &$args, $asset ) {
		if ( in_array( $asset, self::$_excluded, true ) ) :
			$args = null;

		elseif ( preg_match( '/\A[^a-z]|[^a-z0-9_]/', $asset ) ) :
			$args = null;

		/**
		 * Required & Default arguments on basis of asset name
		 */
		elseif ( array_key_exists( $asset, self::$_required['asset'] ) ) :
			$args = is_array( $args ) ? $args : [];
			if ( array_key_exists( $asset, self::$_default['asset'] ) )
				$args = array_merge( self::$_default['asset'][$asset], $args );
			$args = array_merge( $args, self::$_required['asset'][$asset] );

		/**
		 * Required & Default arguments on basis of type
		 */
		elseif ( is_array( $args ) && isset( $args['type'] ) && array_key_exists( $args['type'], self::$_required['type'] ) ) :
			if ( array_key_exists( $args['type'], self::$_default['type'] ) )
				$args = array_merge( self::$_default['type'][$args['type']], $args );
			$args = array_merge( $args, self::$_required['type'][$args['type']] );

		endif;

		if ( ! $args )
			return;
		
		if ( $args && array_key_exists( 'type', $args ) ) {
			if ( $class = $this->get_class_name( $args['type'] ) )
			{
				/**
				 * @uses WPDW\Device\Asset\type_{$type}
				 */
				$args = array_merge( $class::get_defaults(), $args );
				array_walk( $args, $class . '::arguments_walker', $asset );
				$class::arguments_filter( $args );
			}
			else
				$args = null;
		}
	}

	/**
	 * @access private
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
		return array_key_exists( $name, $this->assets ) ? $this->assets[$name] : null;
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
