<?php
namespace WPDW\Device\Asset;

abstract class asset_abstract {
	use \mimosafa\Decoder { getArrayToHtmlString as getHtml; }

	/**
	 * @var string
	 */
	protected $domain;
	protected $type;
	protected $name;
	protected $label;
	protected $description;

	/**
	 * @var boolean
	 */
	protected $multiple = false;
	protected $required = false;
	protected $readonly = false;

	/**
	 * @var array
	 */
	protected $deps;

	/**
	 * @var string
	 */
	protected $delimiter = ', ';

	/**
	 * Array_walk callback function
	 *
	 * @access protected
	 *
	 * @see    WPDW\Device\asset_vars::prepare_arguments()
	 *
	 * @param  mixed  &$arg
	 * @param  string $key
	 * @param  string $asset
	 * @return (void)
	 */
	protected static function arguments_walker( &$arg, $key, $asset ) {
		if ( $key === 'name' ) :
			/**
			 * Asset name
			 * @var string $name
			 */
			$arg = $asset;
		elseif ( in_array( $key, [ 'label', 'description', 'delimiter' ], true ) ) :
			/**
			 * @var string $label|$description|$delimiter
			 */
			$arg = filter_var( $arg, \FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		elseif ( in_array( $key, [ 'multiple', 'required', 'readonly' ], true ) ) :
			/**
			 * @var boolean $multiple|$required|$readonly
			 */
			$arg = filter_var( $arg, \FILTER_VALIDATE_BOOLEAN );
		elseif ( in_array( $key, [ 'deps' ], true ) ) :
			/**
			 * @var array $deps
			 */
			self::_deps_filter( $arg );
		elseif ( in_array( $key, [ 'domain', 'type', ], true ) ) :
			/**
			 * @var string $domain|$type
			 */
			$arg = filter_var( $arg );
		else :
			$arg = null;
		endif;
	}

	/**
	 * @todo
	 *
	 * Asset dependency property filter
	 *
	 * @access private
	 *
	 * @param  mixed &$arg
	 * @return array|boolean
	 */
	private static function _deps_filter( &$arg ) {
		if ( ! is_array( $arg ) ) {
			$arg = false;
		} else {
			foreach ( $arg as $asset => &$param ) {
				$bool = filter_var( $param, \FILTER_VALIDATE_BOOLEAN, \FILTER_NULL_ON_FAILURE );
				if ( isset( $bool ) ) {
					$param = $bool;
				} else {
					unset( $arg[$asset] );
				}
			}
			if ( empty( $arg ) )
				$arg = false;
		}
	}

	/**
	 * Constructor
	 *
	 * @param  WPDW\Device\Asset\verified $args
	 * @return (void)
	 */
	public function __construct( verified $args ) {
		foreach ( $args as $key => $val ) {
			if ( property_exists( $this, $key ) && isset( $val ) )
				$this->$key = $val;
		}
		if ( ! $this->multiple )
			unset( $this->delimiter );
		if ( $this->deps ) {
			$property = \WPDW\_property( $args['domain'] );
			foreach ( $this->deps as $asset => $param ) {
				if ( ! isset( $property->$asset ) ) {
					unset( $this->deps[$asset] );
				}
			}
		}

		if ( ! $this->_validate_after_constructed() )
			unset( $this->type );
	}

	/**
	 * Overloading method: __get
	 * - Read(only) class property
	 *
	 * @access public
	 */
	public function __get( $var ) {
		return property_exists( $this, $var ) ? $this->$var : null;
	}

	/**
	 * If something wrong, $type is not exist.
	 *
	 * @access public
	 *
	 * @return boolean
	 */
	public function _has_no_problem() {
		return isset( $this->type );
	}

	/**
	 * (Latest) Validation
	 * - If necessary, overwrite
	 *
	 * @access protected
	 *
	 * @return boolean
	 */
	protected function _validate_after_constructed() {
		return true;
	}

	/**
	 * @todo
	 *
	 * @access protected
	 *
	 * @param  WP_Post $post
	 * @return boolean
	 */
	protected function check_dependency( \WP_Post $post ) {
		if ( ! $this->deps )
			return true;
		$property = \WPDW\_property( $this->domain );
		foreach ( $this->deps as $asset => $arg ) {
			if ( ! is_array( $arg ) ) {
				if ( filter_var( $arg, \FILTER_VALIDATE_BOOLEAN ) && ! $property->$asset->get( $post ) ) {
					return false;
					break;
				}
			} else {
				//
			}
		}
		return true;
	}

}
