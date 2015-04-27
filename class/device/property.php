<?php
namespace WPDW\Device;

/**
 * @uses WPDW\Util\Singleton
 * @uses WPDW\Device\Module\Functions
 */
trait property {
	use \WPDW\Util\Singleton;
	use Module\Functions;

	/**
	 * @var array
	 */
	private $data = [];

	/**
	 * Words that are not allowed to be used as an asset name
	 * @var array
	 */
	private static $excluded = [
		'_edit_last', '_edit_lock', '_wp_old_slug', '_thumbnail_id',
		'_wp_attached_file', '_wp_page_template', '_wp_attachment_metadata',
	];

	/**
	 * Constructor
	 *
	 * @access protected
	 */
	protected function __construct() {
		if ( $this->isDefined( 'assets' ) )
			array_walk( $this->assets, [ &$this, 'prepare_assets' ] );
		_var_dump( $this );
	}

	/**
	 * @access private
	 *
	 * @param  array  &$args
	 * @param  string $asset
	 */
	private function prepare_assets( &$args, $asset ) {
		if ( preg_match( '/\A[^a-z]|[^a-z0-9_]/', $asset ) ) :
			$args = null;
		elseif ( in_array( $asset, self::$excluded, true ) ) :
			$args = null;
		elseif ( array_key_exists( 'type', $args ) ) :
			if ( $class = $this->get_class_name( $args['type'] ) )
			{
				$args  = array_merge( $class::get_defaults(), $args );
				array_walk( $args, $class . '::arguments_walker', $asset );
			}
			else
				$args = null;
		endif;
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
		if ( array_key_exists( $name, $this->data ) )
			return true;
		if ( $setting = $this->get_setting( $name ) ) {
			$class = $this->get_class_name( $setting['type'] );
			$this->data[$name] = new $class( $setting );
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
		return isset( $this->$name ) ? $this->data[$name] : null;
	}

}
