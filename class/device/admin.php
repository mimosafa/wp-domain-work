<?php
namespace WPDW\Device;

/**
 * @uses WPDW\Device\common
 */
trait admin {
	use Module\Initializer, Module\Functions;
	use \WPDW\Util\Array_Function;

	/**
	 * @var string
	 */
	private $domain;

	/**
	 * @var WP_Domain\{$domain}\property
	 */
	private $property;

	/**
	 * @var array
	 */
	private $done_assets = [];

	/**
	 * Constructor
	 *
	 * @access private
	 * @param  array $args {
	 *     @see  WPDW\Router
	 *     
	 *     @type string $domain
	 *     ..
	 * }
	 * @return  (void)
	 */
	private function __construct( Array $args ) {
		$this->domain = $args['domain'];
		$class = 'WP_Domain\\' . $this->domain . '\\property';
		if ( class_exists( $class ) )
			$this->property = $class::getInstance();
		$this->init_page();
	}

	/**
	 * Initialize admin page
	 * @access private
	 */
	private function init_page() {
		global $pagenow;
		if ( in_array( $pagenow, [ 'edit.php', 'post.php', 'post-new.php' ], true ) )
			$this->init_post_type( $pagenow );
		else if ( $pagenow === 'edit-tags.php' )
			$this->init_taxonomy( $pagenow );
	}

	/**
	 * @access private
	 * @param  string $pagenow edit.php|post.php|post-new.php
	 */
	private function init_post_type( $pagenow ) {
		if ( $pagenow === 'edit.php' ) {
			/**
			 * Posts list page
			 */
		} else {
			/**
			 * Edit post page, add post page
			 */
			if ( $this->isDefined( 'meta_boxes' ) ) {
				/**
				 * Meta boxes
				 * @uses  WPDW\Device\Admin\meta_boxes::add()
				 */
				$metabox = new Admin\meta_boxes( $this->domain );
				foreach ( $this->meta_boxes as $meta_box_args ) {
					if ( $args = $this->meta_box_arguments( $meta_box_args ) ) {
						$metabox->add( $args );
					}
				}
			}
		}
		new Admin\save_post( $this->domain );
	}

	/**
	 * @access private
	 *
	 * @uses WPDW\Util\Array_Function::array_flatten()
	 *
	 * @param  array $args
	 * @return array
	 */
	private function meta_box_arguments( Array $args ) {
		$args = filter_var_array( $args, $this->get_filter_definition( 'meta_box' ), false );
		if ( array_key_exists( 'asset', $args ) ) {
			$assets = is_array( $args['asset'] ) ? array_filter( self::array_flatten( $args['asset'], true ) ) : (array) $args['asset'];
			if ( ! array_key_exists( 'title', $args ) || ! $args['title'] ) {
				$args['title'] = implode( ' / ', array_map( function( $asset ) {
					$setting = $this->property->get_setting( $asset );
					return $setting['label'] ?: ucwords( str_replace( '_', ' ', $asset ) );
				}, $assets ) );
			}
			$args['asset'] = count( $assets ) > 1 ? $assets : array_shift( $assets );
		}
		$args = array_filter( $args );
		if ( array_key_exists( 'asset', $args ) )
			return $args;
		else if ( array_key_exists( 'callback', $args ) && array_key_exists( 'id', $args ) )
			return $args;
		else
			return null;
	}

	/**
	 * @access private
	 *
	 * @param  string $context (Optional)
	 * @return array
	 */
	private function get_filter_definition( $context = null ) {
		if ( ! $context ) {
			/**
			 * Common filter definition
			 * @var array
			 */
			static $common;
			if ( ! $common ) {
				// asset
				$assetVar = function( $var ) {
					if ( ! $this->property || in_array( $var, $this->done_assets, true ) )
						return null;
					if ( ! $this->property->get_setting( $var ) )
						return null;
					$this->done_assets[] = $var;
					return $var;
				};
				// callback
				$callbackVar = function( $var ) {
					return is_callable( $var ) ? $var : null;
				};
				$common = [
					'id' => [ 'filter' => \FILTER_VALIDATE_REGEXP, 'options' => [ 'regexp' => '/\A[a-z][a-z0-9_\-]+\z/', 'default' => null ] ],
					'title' => \FILTER_SANITIZE_FULL_SPECIAL_CHARS,
					'callback' => [ 'filter' => \FILTER_CALLBACK, 'options' => $callbackVar ],
					'asset' => [ 'filter' => \FILTER_CALLBACK, 'options' => $assetVar ],
					'description' => \FILTER_SANITIZE_FULL_SPECIAL_CHARS,
				];
			}
			return $common;
		} else if ( $context === 'meta_box' ) {
			/**
			 * Filter definition for meta box arguments
			 * @var array
			 */
			static $metabox;
			if ( ! $metabox ) {
				$metabox = $this->get_filter_definition();
				// context
				$contextVar = function( $var ) {
					return in_array( $var, [ 'normal', 'advanced', 'side' ], true ) ? $var : null;
				};
				// priority
				$priorityVar = function( $var ) {
					return in_array( $var, [ 'high', 'core', 'default', 'low' ], true ) ? $var : null;
				};
				$metabox['context']  = [ 'filter' => \FILTER_CALLBACK, 'options' => $contextVar ];
				$metabox['priority'] = [ 'filter' => \FILTER_CALLBACK, 'options' => $priorityVar ];
			}
			return $metabox;
		}
	}

	/**
	 * @access private
	 */
	private function init_taxonomy( $pagenow ) {
		//
	}
	
}
