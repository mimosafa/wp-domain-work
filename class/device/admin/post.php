<?php
namespace WPDW\Device\Admin;

/**
 * Inherited by
 * - WPDW\Device\Admin\meta_boxes
 * - WPDW\Device\Admin\edit_form_advamced
 */
abstract class post {
	use \WPDW\Util\Array_Function;

	const FORM_ID_PREFIX = 'wpdw-form-';
	const BOX_ID_PREFIX  = 'wpdw-meta-box-';
	const DIV_ID_PREFIX  = 'wpdw-div-';

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
	 * @uses   WPDW\_property()
	 * @see    wp-domain-work/inc/functions.php
	 *
	 * @param  string $domain
	 */
	public function __construct( $domain ) {
		if ( ! $domain = filter_var( $domain ) )
			return;
		$this->property = \WPDW\_property( $domain );
		if ( ! self::$template )
			self::$template = new template( $domain );

		static $done = false;
		if ( ! $done ) {
			/**
			 * Exclude wrote custom fields from post custom meta box
			 */
			add_filter( 'is_protected_meta', [ $this, 'is_protected_meta' ], 10, 3 );
			add_action( 'admin_enqueue_scripts', [ &$this, 'localize_form_data' ], 8 );
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
	protected function prepare_arguments( Array $args ) {
		$args = filter_var_array( $args, $this->get_filter_definition() );
		if ( $args['asset'] ) {
			/**
			 * Set title if not defined
			 */
			if ( ! $args['title'] ) {
				$assets = is_array( $args['asset'] ) ? array_filter( self::flatten( $args['asset'], true ) ) : (array) $args['asset'];
				$args['title'] = implode(
					' / ',
					array_map( function( $asset ) {
						return $this->property->get_setting( $asset )['label'];
					}, $assets )
				);
				$args['asset'] = count( $assets ) > 1 ? $assets : array_shift( $assets );
			}

			//$setting = $this->property->get_setting( $args['asset'] );
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
	 * Common arguments filter definition
	 *
	 * @access protected
	 *
	 * @return array
	 */
	protected function get_filter_definition() {
		static $def;
		if ( ! $def ) {
			// Asset
			$assetVar = function( $var ) use ( &$assetVar ) {
				if ( ! $this->property || in_array( $var, self::$done_assets, true ) )
					return null;
				if ( ! $setting = $this->property->get_setting( $var ) )
					return null;
				if ( $var[0] !== '_' )
					self::$done_assets[] = $var;

				/**
				 * Find asset recursively
				 */
				if ( isset( $setting['assets'] ) && $setting['type'] !== 'complex' ) {
					foreach ( $setting['assets'] as $asset )
						$assetVar( $asset );
				}

				return $var;
			};

			// Callback
			$callbackVar = function( $var ) {
				return is_callable( $var ) ? $var : null;
			};

			$def = [
				'id'    => [ 'filter' => \FILTER_VALIDATE_REGEXP, 'options' => [ 'regexp' => '/\A[a-z][a-z0-9_\-]+\z/', 'default' => null ] ],
				'title' => \FILTER_SANITIZE_FULL_SPECIAL_CHARS,
				'callback' => [ 'filter' => \FILTER_CALLBACK, 'options' => $callbackVar ],
				'asset'    => [ 'filter' => \FILTER_CALLBACK, 'options' => $assetVar ],
				'description' => \FILTER_SANITIZE_FULL_SPECIAL_CHARS,
			];
		}
		return $def;
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
			$recipe = [ 'type' => '_plural_assets', 'assets' => [] ];
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

	public function localize_form_data() {
		if ( self::$done_assets ) {
			$data = [];
			foreach ( self::$done_assets as $asset ) {
				$data[$asset] = $this->property->$asset->get_recipe();
			}
			\WPDW\Scripts::add_data( 'forms', $data );
			\WPDW\Scripts::add_data( 'form_id_prefix', self::FORM_ID_PREFIX );
		}
	}

}
