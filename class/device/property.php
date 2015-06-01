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
	private $_assets_data = [];

	/**
	 * @var WPDW\Device\Asset\provision
	 */
	private $_assets_provision;

	/**
	 * Constructor
	 *
	 * @access protected
	 *
	 * @uses   WPDW\Device\Asset\provision
	 */
	protected function __construct() {
		if ( $this->isDefined( 'assets' ) ) {
			$domain = explode( '\\', __CLASS__ )[1];
			$this->_assets_provision = new Asset\provision( $domain );
			$this->_assets_provision->sort_assets( $this->assets );
			array_walk( $this->assets, [ &$this->_assets_provision, 'prepare_assets' ] );
			
			$this->assets = array_filter( $this->assets );
		}
		#_var_dump( $this );
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
	 * @uses   WPDW\Device\Asset\provision::get_class_name()
	 *
	 * @param  string $name
	 * @return boolean
	 */
	public function __isset( $name ) {
		if ( array_key_exists( $name, $this->_assets_data ) )
			return true;
		if ( $setting = $this->get_setting( $name ) ) {
			$class = $this->_assets_provision->get_class_name( $setting['type'] );
			$this->_assets_data[$name] = new $class( $setting );
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
		return isset( $this->$name ) ? $this->_assets_data[$name] : null;
	}

}
