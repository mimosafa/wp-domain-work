<?php
namespace WPDW\Device\Admin;

/**
 * Class for printing admin form.
 * - Used @ WPDW\Device\Admin\meta_boxes class
 *
 * @uses mimosafa\Decoder
 * @uses WPDW\WP\nonce
 */
class template {
	use \mimosafa\Decoder;

	/**
	 * Form elements id prefix
	 * @var string
	 */
	private $form_id_prefix = 'wp-domain-work-form-';

	/**
	 * @var WPDW\WP\nonce
	 */
	private $nonce;

	/**
	 * Constructor
	 *
	 * @param  string $context
	 * @return (void)
	 */
	public function __construct( $domain ) {
		if ( ! $domain = filter_var( $domain ) )
			return;
		$this->form_id_prefix .= $domain . '-';
		/**
		 * Nonce gen
		 * - $domain must be the same as when saving
		 * @see WPDW\Device\Admin\save_post::__construct()
		 */
		$this->nonce = new \WPDW\WP\nonce( $domain );
	}

	/**
	 * @access public
	 *
	 * @param  array $args
	 * @return (void)
	 */
	public function output( Array $args ) {
		if ( $args['type'] === 'posts' ) {
			//
		} else {
			$this->output_form( $args );
		}
	}

	/**
	 * @access private
	 *
	 * @uses   mimosafa\Decoder::getArrayToHtmlString()
	 *
	 * @param  array $args
	 * @return (void)
	 */
	private function output_form( Array $args ) {
		if ( $dom_array = $this->generate_dom_array( $args ) )
			echo $this->getArrayToHtmlString( $dom_array );
	}

	/**
	 * Generate DOM array method
	 *
	 * @access private
	 *
	 * @param  array $args {
	 *     //
	 * }
	 * @return array
	 */
	private function generate_dom_array( Array $args ) {

		static $name = '';
		static $id   = '';
		static $table  = [];

		/**
		 * @var string
		 */
		$type = $args['type'];

		/**
		 * Return array
		 * @var array
		 */
		$return = [];

		if ( array_key_exists( 'assets', $args ) ) {

			if ( $type === 'group' && empty( $table ) ) {
				$table = [
					'element' => 'table',
					'attribute' => [ 'class' => 'form-table' ],
					'children' => [
						[ 'element' => 'tbody', 'children' => [] ]
					]
				];
				$tr_wrapper =& $table['children'][0]['children'];

				foreach ( $args['assets'] as $child_args ) {
					$id = $this->form_id_prefix . $child_args['name'];
					$_id = $id;
					$label = $child_args['label'] ?: ucwords( str_replace( '_', ' ', $child_args['name'] ) );

					/**
					 * Add argument for group member
					 */
					$child_args['member_of'] = 'group';

					if ( $child_dom = $this->generate_dom_array( $child_args ) ) {
						$tr_wrapper[] = [
							'element'  => 'tr',
							'children' => [
								[
									'element'  => 'th',
									'children' => [
										[
											'element'   => 'label',
											'attribute' => [ 'for' => esc_attr( $_id ) ],
											'text'      => esc_html( $label )
										]
									]
								], [
									'element'  => 'td',
									'children' => $child_dom
								]
							]
						];
					}
					$name = $id = ''; // init vars
				}

				if ( ! empty( $tr_wrapper ) ) {
					$return[] = $table;
				}
				$table = [];
			}

		} else {

			$name = $name ?: $args['name'];
			$id = $id ?: $this->form_id_prefix . $name;

			$method = $type . '_dom_array';
			$dom = method_exists( __CLASS__, $method ) ? $this->$method( $id, $name, $args ) : [];

			$return[] = $dom;
			$this->nonce_dom_array( $return, $args['name'] );

			$id = $name = '';

		}

		/**
		 * Form description
		 */
		if ( array_key_exists( 'description', $args ) && $args['description'] ) {
			$desc = [
				'element' => 'p',
				'text'    => esc_html( $args['description'] ),
			];
			if ( 'group' === $type ) {
				array_unshift( $return, $desc );
			} else {
				$desc['attribute'] = [ 'class' => 'description' ];
				$return[] = $desc;
			}
		}

		return $return;
	}

	/**
	 * Type: string - Generate DOM array method
	 *
	 * @access private
	 *
	 * @param  string $id
	 * @param  string $name
	 * @param  array  $args
	 * @return array
	 */
	private function string_dom_array( $id, $name, Array $args ) {
		$attr = [
			'type'  => 'text',
			'id'    => esc_attr( $id ),
			'name'  => esc_attr( $name ),
		];

		if ( isset( $args['value'] ) )
			$attr['value'] = esc_attr( $args['value'] );
		if ( $args['max'] )
			$attr['maxlength'] = esc_attr( $args['max'] );
		if ( $args['readonly'] )
			$attr['readonly'] = 'readonly';

		$attr['class'] = 'regular-text';

		return [ 'element' => 'input', 'attribute' => $attr ];
	}

	/**
	 * Type: integer - Generate DOM array method
	 *
	 * @access private
	 *
	 * @param  string $id
	 * @param  string $name
	 * @param  array  $args
	 * @return array
	 */
	private function integer_dom_array( $id, $name, Array $args ) {
		$attr = [
			'type' => 'number',
			'id'   => esc_attr( $id ),
			'name' => esc_attr( $name ),
		];

		if ( isset( $args['value'] ) )
			$attr['value'] = esc_attr( $args['value'] );
		if ( isset( $args['min'] ) )
			$attr['min'] = esc_attr( $args['min'] );
		if ( isset( $args['max'] ) )
			$attr['max'] = esc_attr( $args['max'] );
		if ( $args['readonly'] )
			$attr['readonly'] = 'readonly';

		return [ 'element' => 'input', 'attribute' => $attr ];
	}

	/**
	 * Type: datetime - Generate DOM array method
	 *
	 * @access private
	 *
	 * @param  string $id
	 * @param  string $name
	 * @param  array  $args
	 * @return array
	 */
	private function datetime_dom_array( $id, $name, Array $args ) {
		$attr = [
			'type' => $args['input_type'],
			'id'   => esc_attr( $id ),
			'name' => esc_attr( $name ),
		];

		if ( isset( $args['value'] ) )
			$attr['value'] = esc_attr( $args['value'] );
		if ( isset( $args['step'] ) )
			$attr['step'] = esc_attr( $args['step'] );
		/*
		if ( isset( $args['min'] ) )
			$attr['min'] = esc_attr( $args['min'] );
		if ( isset( $args['max'] ) )
			$attr['max'] = esc_attr( $args['max'] );
		*/
		if ( $args['readonly'] )
			$attr['readonly'] = 'readonly';

		return [ 'element' => 'input', 'attribute' => $attr ];
	}

	/**
	 * Type: boolean - Generate DOM array method
	 *
	 * @access private
	 *
	 * @param  string $id
	 * @param  string $name
	 * @param  array  $args
	 * @return array
	 */
	private function boolean_dom_array( $id, $name, Array $args ) {
		$el = [
			'element' => 'input',
			'attribute' => [
				'type'  => 'checkbox',
				'id'    => esc_attr( $id ),
				'name'  => esc_attr( $name ),
				'value' => 1,
			],
		];
		if ( $args['value'] )
			$el['attribute']['checked'] = 'checked';
		if ( $args['readonly'] )
			$el['attribute']['class'] = 'wpdw-checkbox-readonly';

		if ( isset( $args['member_of'] ) && $args['member_of'] === 'group' ) {
			return $el;
		}

		$return = [
			'element'   => 'label',
			'attribute' => [ 'for' => esc_attr( $id ), /*'class' => \WPDW_FORM_PREFIX . 'checkbox'*/ ],
			'children'  => [ $el ],
			'text'      => esc_html( $args['label'] ),
		];
		return $return;
	}

	/**
	 * Generate nonce dom array
	 *
	 * @see  https://github.com/WordPress/WordPress/blob/4.1-branch/wp-includes/functions.php#L1366
	 * @see  https://github.com/WordPress/WordPress/blob/4.1-branch/wp-includes/functions.php#L1390
	 * 
	 * @param  array  &$dom
	 * @param  string $field
	 * @return (void)
	 */
	private function nonce_dom_array( &$dom, $field ) {
		$name = esc_attr( $this->nonce->get_nonce( $field ) );
		$nonce = [
			'element' => 'input',
			'attribute' => [
				'type' => 'hidden',
				'id' => $name,
				'name' => $name,
				'value' => esc_attr( $this->nonce->create_nonce( $field ) )
			]
		];
		$refer = [
			'element' => 'input',
			'attribute' => [
				'type' => 'hidden',
				'name' => '_wp_http_referer',
				'value' => esc_attr( wp_unslash( $_SERVER['REQUEST_URI'] ) )
			]
		];
		$dom[] = $nonce;
		$dom[] = $refer;
	}

}
