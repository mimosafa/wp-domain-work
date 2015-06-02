<?php
namespace WPDW\Device\Admin;

/**
 * Inherited by
 * - WPDW\Device\Admin\meta_boxes
 * - WPDW\Device\Admin\edit_form_advamced
 */
abstract class post {

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
	protected static $admin_forms = [];
	protected static $asset_values = [];

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
		$args['asset'] = \WPDW\Util\Array_Function::flatten( (array) $args['asset'], true );
		if ( ! $args['asset'] )
			return;

		$id = '';
		$ttl = $args['title'] ? null : '';
		$assetWalker = function( $asset ) use ( &$id, &$ttl ) {
			self::$admin_forms[] = $asset;
			$id .= $asset . '-';
			if ( is_string( $ttl ) )
				$ttl .= $this->property->get_setting( $asset )['label'] . ' / ';
		};
		array_walk( $args['asset'], $assetWalker );

		$args['id'] = rtrim( $id, '-' );
		if ( $ttl )
			$args['title'] = rtrim( $ttl, '/ ' );

		$args['asset'] = count( $args['asset'] ) > 1 ? $args['asset'] : array_shift( $args['asset'] );

		return array_filter( $args );
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

			$def = [
				'title' => \FILTER_SANITIZE_FULL_SPECIAL_CHARS,
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
		if ( self::$admin_forms && self::$done_assets ) {
			$data = [];
			foreach ( self::$done_assets as $asset ) {
				$data[$asset] = $this->property->$asset->get_recipe();
			}
			\WPDW\Scripts::add_data( 'assets', $data );
			\WPDW\Scripts::add_data( 'adminForms', self::$admin_forms );
			\WPDW\Scripts::add_data( 'assetValues', self::$asset_values );
			\WPDW\Scripts::add_data( 'form_id_prefix', self::FORM_ID_PREFIX );
		}
	}

}
