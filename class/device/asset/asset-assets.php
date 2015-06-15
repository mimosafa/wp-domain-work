<?php
namespace WPDW\Device\Asset;

abstract class asset_assets extends asset_abstract {
	use \WPDW\Util\Array_Function;

	/**
	 * @var array
	 */
	protected $assets;

	/**
	 * @var string
	 */
	protected $glue = ' ';

	/**
	 * @var string
	 */
	protected $format;

	/**
	 * @var string
	 */
	protected $admin_form_style;

	/**
	 * @access public
	 *
	 * @param  mixed $array
	 * @return array|null
	 */
	public function filter_input( $array ) {
		if ( ! is_array( $array ) )
			return null;

		$property = \WPDW\_property( $this->domain );
		$return = [];
		foreach ( $this->assets as $asset ) {
			$val = isset( $array[$asset] ) ? $array[$asset] : '';
			$assetObj = $property->$asset;
			$return[$asset] = $assetObj->filter_input( $val );
		}
		return $return;
	}

	/**
	 * Get value
	 *
	 * @access public
	 *
	 * @uses   WPDW\Device\Asset\asset_abstract::check_dependency()
	 *
	 * @param  int|WP_Post $post
	 * @return mixed
	 */
	public function get( $post ) {
		if ( ! $post = get_post( $post ) )
			return;
		if ( ! $this->check_dependency( $post ) )
			return null;

		$value = [];
		$property = \WPDW\_property( $this->domain );
		foreach ( $this->assets as $asset ) {
			$value[$asset] = $property->$asset->get( $post );
		}
		return $value;
	}

	/**
	 * Update value
	 *
	 * @access public
	 *
	 * @uses   WPDW\_property()
	 *
	 * @param  int|WP_Post $post
	 * @param  array $value
	 * @return (void)
	 */
	public function update( $post, $value ) {
		if ( ! is_array( $value ) || ! $post = get_post( $post ) )
			return;
		if ( ! $this->check_dependency( $post ) )
			return null;
		$value = $this->filter_input( $value );
		$this->update_assets( $post, $value );
	}

	/**
	 * @access protected
	 *
	 * @param  WP_Post $post
	 * @param  array   $args
	 * @return (void)
	 */
	protected function update_assets( \WP_Post $post, Array $values ) {
		$property = \WPDW\_property( $this->domain );
		foreach ( $values as $asset => $val ) {
			$property->$asset->update( $post, $val );
		}
	}

	public function print_column( $value, $post_id ) {
		//
	}

	/**
	 * Array_walk callback function
	 *
	 * @see    WPDW\Device\asset_vars::prepare_arguments()
	 *
	 * @access protected
	 *
	 * @uses   WPDW\Util\Array_Function::flatten()
	 *
	 * @param  mixed  &$arg
	 * @param  string $key
	 * @param  string $asset
	 * @return (void)
	 */
	protected static function arguments_walker( &$arg, $key, $asset ) {
		if ( $key === 'assets' ) :
			/**
			 * @var array $assets
			 */
			$arg = self::flatten( filter_var( $arg, \FILTER_DEFAULT, \FILTER_REQUIRE_ARRAY ), true );
		elseif ( $key === 'multiple' ) :
			/**
			 * @var boolean $multiple false
			 */
			$arg = false;
		elseif ( in_array( $key, [ 'glue' ], true ) ) :
			/**
			 * @var string $glue
			 */
			$arg = filter_var( $arg, \FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		else :
			parent::arguments_walker( $arg, $key, $asset );
		endif;
	}

	/**
	 * @access protected
	 *
	 * @param  array $args
	 * @return boolean
	 */
	protected static function is_met_requirements( Array $args ) {
		return $args['assets'] ? true : false;
	}

	/**
	 * Render html element as form element
	 *
	 * @access public
	 *
	 * @uses   mimosafa\Decoder::getArrayToHtmlString() as getHtml()
	 *
	 * @param  WP_Post $post
	 * @return (void)
	 */
	public function admin_form_element( \WP_Post $post ) {
		$table = [
			'element'   => 'table',
			'attribute' => [ 'class' => 'form-table' ],
			'children'  => [ [ 'element' => 'tbody', 'children' => [] ] ]
		];
		$rows =& $table['children'][0]['children'];
		$property = \WPDW\_property( $this->domain );
		foreach ( $this->assets as $asset ) {
			$label = $property->get_setting( $asset )['label'];
			$instance = $property->$asset;
			$value = $instance->get( $post );

			$label_el = [ 'element' => 'label', 'text' => esc_html( $label ) ];
			$form_el = $instance->admin_form_element_dom_array( $value, $this->name );

			$rows[] = [
				'element' => 'tr',
				'children' => [
					[ 'element' => 'th', 'children' => [ $label_el ] ],
					[ 'element' => 'td', 'children' => $form_el ]
				]
			];
		}
		return self::getHtml( [ $table ] );
	}

}
