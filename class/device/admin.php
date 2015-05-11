<?php
namespace WPDW\Device;

/**
 * @uses   WPDW\Device\Module\Methods::is()
 * @uses   WPDW\Device\Module\Methods::isDefined()
 * @uses   WPDW\Util\Array_Function::array_flatten()
 * @global $pagenow
 */
trait admin {
	use \WPDW\Util\Singleton, Module\Methods;
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
	 *
	 * @uses   WPDW\_property_object()
	 * @see    wp-domain-work/inc/functions.php
	 *
	 * @param  array $args {
	 *     @see  WPDW\Router
	 *     
	 *     @type string $domain
	 *     ..
	 * }
	 * @return  (void)
	 */
	private function __construct() {
		$this->domain = explode( '\\', __CLASS__ )[1];
		$this->property = \WPDW\_property_object( $this->domain );

		$this->init();
	}

	/**
	 * Initialize admin page
	 * 
	 * @access private
	 *
	 * @uses   WP_Domain\{$domain}\status
	 */
	private function init() {
		global $pagenow;
		if ( in_array( $pagenow, [ 'edit.php', 'post.php', 'post-new.php' ], true ) ) {
			/**
			 * Domain that registered as post_type
			 */
			if ( $statusInstance = \WPDW\_status_object( $this->domain ) )
				/**
				 * Post statuses
				 */
				$statusInstance->init();

			$this->init_post_type( $pagenow );

			if ( $this->done_assets && $pagenow !== 'edit.php' )
				/**
				 * Exclude wrote custom fields from post custom meta box
				 */
				add_filter( 'is_protected_meta', [ $this, 'is_protected_meta' ], 10, 3 );

		} else if ( $pagenow === 'edit-tags.php' ) {
			/**
			 * Domain that registered as taxonomy
			 */
			$this->init_taxonomy( $pagenow );
		}
		add_action( 'admin_enqueue_scripts', [ $this, 'scripts_handler' ] );
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
			if ( $this->isDefined( 'columns' ) ) {
				/**
				 * Post columns
				 * @uses  WPDW\Device\Admin\posts_columns
				 */
				$columns = new Admin\posts_columns( $this->domain );
				foreach ( $this->columns as $name => $args )
					$columns->add( $name, $args );
			}
		} else {
			/**
			 * Edit post page, add post page
			 */
			if ( $this->isDefined( 'meta_boxes' ) ) {
				/**
				 * Meta boxes
				 * @uses  WPDW\Device\Admin\meta_boxes
				 */
				$metabox = new Admin\meta_boxes( $this->domain );
				foreach ( $this->meta_boxes as $meta_box_args ) {
					if ( $args = $this->meta_box_arguments( $meta_box_args ) )
						$metabox->add( $args );
				}
			}
			if ( $this->is( 'attribute_meta_box' ) ) {
				/**
				 * Attribute meta box
				 * @uses  WPDW\Device\Admin\attribute_meta_box
				 */
				$args = is_array( $this->attribute_meta_box ) ? $this->attribute_meta_box : [];
				$attrFilter = function( $attr ) { return ! in_array( $attr, $this->done_assets, true ); };
				if ( $attributes = array_filter( [ 'post_parent', 'menu_order' ], $attrFilter ) ) {
					$args = array_merge( $args, [ 'attributes' => $attributes ] );
					new Admin\attribute_meta_box( $this->domain, $args );
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
			if ( ! array_key_exists( 'title', $args ) ) {
				$args['title'] = implode(
					' / ',
					array_map( function( $asset ) {
						$setting = $this->property->get_setting( $asset );
						return $setting['label'] ?: ucwords( str_replace( '_', ' ', $asset ) );
					}, $assets )
				);
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
		if ( $meta_type === 'post' && in_array( $meta_key, $this->done_assets ) ) {
			$protected = true;
		}
		return $protected;
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

	public function scripts_handler( $pagenow ) {
		global $pagenow;
		if ( in_array( $pagenow, [ 'post.php', 'post-new.php'], true ) ) {
			wp_enqueue_style( 'wpdw-post', \WPDW_PLUGIN_URL . '/css/post.css', [], '', 'screen' );
		}
	}

}
