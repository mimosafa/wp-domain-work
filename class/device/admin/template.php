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
	 */
	const FORM_ID_PREFIX = 'wp-domain-work-form-';

	/**
	 * @var string
	 */
	private $context;

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
		if ( ! is_string( $domain ) || ! $domain )
			return;
		$this->context = $domain;
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
	 * @access private
	 *
	 * @param  array $args {
	 *
	 * }
	 */
	private function generate_dom_array( Array $args ) {

		static $name = '';
		static $id   = '';
		static $table  = [];
		static $inline = [];

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
					$id = self::FORM_ID_PREFIX . $this->context . '-' . $child_args['name'];
					$label = $child_args['label'] ?: ucwords( str_replace( '_', ' ', $child_args['name'] ) );
					if ( $child_dom = $this->generate_dom_array( $child_args ) ) {
						$tr_wrapper[] = [
							'element' => 'tr',
							'children' => [
								[
									'element' => 'th',
									'children' => [
										[
											'element' => 'label',
											'attribute' => [ 'for' => esc_attr( $id ) ],
											'text' => esc_html( $label )
										]
									]
								], [
									'element' => 'td',
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
				$table = []; // init vars
			#} else if ( $type === 'set' ) {
				//
			}
		} else {
			$name = $name ?: $args['name'];
			$id = $id ?: self::FORM_ID_PREFIX . $this->context . '-' . $name;

			$dom = [ 'element' => '', 'attribute' => [] ];
			$attr =& $dom['attribute'];
			if ( in_array( $type, [ 'string', 'integer', 'date', 'time', 'datetime' ] ) ) {
				$dom['element'] = 'input';
				$attr['id'] = esc_attr( $id );
				$attr['name'] = esc_attr( $name );
				if ( in_array( $type, [ 'string', 'integer'] ) ) {
					if ( array_key_exists( 'value', $args ) ) {
						$attr['value'] = esc_attr( $args['value'] );
					}
					if ( $type === 'string' ) {
						$attr['type'] = 'text';
						$attr['class'] = 'regular-text';
						$attr['placeholder'] = esc_html( $args['label'] );
					} else if ( $type === 'integer' ) {
						$attr['type']  = 'number';
						$attr['class'] = 'small-text';
					}
				}
			}

			/**
			 *
			 */
			$return[] = $dom;
			$this->nonce_dom_array( $return, $args['name'] );
		}
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
