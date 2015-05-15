<?php
namespace WPDW\Device\Admin;

/**
 * Inherited by
 * - WPDW\Device\Admin\meta_boxes
 * - WPDW\Device\Admin\edit_form_advamced
 */
abstract class post {
	use \WPDW\Util\Array_Function;

	/**
	 * @var  WP_Domain\{$domain}\property
	 */
	protected $property;

	/**
	 * @var WPDW\Device\Admin\template
	 */
	protected static $template;

	/**
	 * @var array
	 */
	protected static $done_assets = [];

	/**
	 * Constructor
	 *
	 * @access public
	 *
	 * @uses   WPDW\_property_object()
	 * @see    wp-domain-work/inc/functions.php
	 *
	 * @param  string $domain
	 */
	public function __construct( $domain ) {
		if ( ! $domain = filter_var( $domain ) )
			return;
		$this->property = \WPDW\_property_object( $domain );
		if ( ! self::$template )
			self::$template = new template( $domain );

		static $done = false;
		if ( ! $done ) {
			/**
			 * Exclude wrote custom fields from post custom meta box
			 */
			add_filter( 'is_protected_meta', [ $this, 'is_protected_meta' ], 10, 3 );
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
	protected function prepare_arguments( $context, Array $args ) {
		$args = filter_var_array( $args, $this->get_filter_definition( $context ) );
		if ( $args['asset'] ) {
			$assets = is_array( $args['asset'] ) ? array_filter( self::flatten( $args['asset'], true ) ) : (array) $args['asset'];
			if ( ! $args['title'] ) {
				$args['title'] = implode(
					' / ',
					array_map( function( $asset ) {
						return $this->property->get_setting( $asset )['label'];
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
	 * @access protected
	 *
	 * @param  string $context (Optional)
	 * @return array
	 */
	protected function get_filter_definition( $context = null ) {
		if ( ! $context ) :

			/**
			 * Common filter definition
			 * @var array
			 */
			static $common;
			if ( ! $common ) {
				// asset
				$assetVar = function( $var ) {
					if ( ! $this->property || in_array( $var, self::$done_assets, true ) )
						return null;
					if ( ! $this->property->get_setting( $var ) )
						return null;
					self::$done_assets[] = $var;
					return $var;
				};
				// callback
				$callbackVar = function( $var ) {
					return is_callable( $var ) ? $var : null;
				};
				$common = [
					'id'    => [ 'filter' => \FILTER_VALIDATE_REGEXP, 'options' => [ 'regexp' => '/\A[a-z][a-z0-9_\-]+\z/', 'default' => null ] ],
					'title' => \FILTER_SANITIZE_FULL_SPECIAL_CHARS,
					'callback' => [ 'filter' => \FILTER_CALLBACK, 'options' => $callbackVar ],
					'asset'    => [ 'filter' => \FILTER_CALLBACK, 'options' => $assetVar ],
					'description' => \FILTER_SANITIZE_FULL_SPECIAL_CHARS,
				];
			}
			return $common;
		
		elseif ( $context === 'meta_box' ) :

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

		elseif ( $context === 'edit_form' ) :

			/**
			 * Filter definition for (direct) edit form arguments
			 * @var array
			 */
			static $editForm;
			if ( ! $editForm ) {
				$editForm = $this->get_filter_definition();
				// context
				$contextVar = function( $var ) {
					return in_array( $var, [ 'top', 'before_permalink', 'after_title', 'after_editor' ], true ) ? $var : null;
				};
				$editForm['context'] = [ 'filter' => \FILTER_CALLBACK, 'options' => $contextVar ];
			}
			return $editForm;
		
		endif;
	}

	/**
	 * @access protected
	 *
	 * @param  string|array $asset
	 * @param  array $args
	 * @param  WP_Post $post
	 * @return array
	 */
	protected function get_recipe( $asset, Array $args, \WP_Post $post ) {
		if ( is_array( $asset ) ) {
			$recipe = [ 'type' => 'group', 'assets' => [] ];
			foreach ( $asset as $a )
				$recipe['assets'][] = $this->property->$a->get_recipe( $post );
		} else {
			$recipe = $this->property->$asset->get_recipe( $post );
		}
		// description
		if ( array_key_exists( 'description', $args ) )
			$recipe = array_merge( $recipe, [ 'description' => $args['description'] ] );

		return $recipe;
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
		if ( self::$done_assets && $meta_type === 'post' ) {
			if ( in_array( $meta_key, self::$done_assets, true ) )
				$protected = true;
		}
		return $protected;
	}

}
