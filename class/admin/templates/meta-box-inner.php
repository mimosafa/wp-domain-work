<?php

namespace WP_Domain_Work\Admin\templates;

/**
 * @uses WP_Domain_Work\WP\nonce
 * @uses mimosafa\Decoder
 * @uses WP_Domain_Work\Property\(property type)
 */
class meta_box_inner {

	/**
	 * @var string
	 */
	private $context;

	/**
	 * @var bool
	 */
	private $_post_new;

	/**
	 * @var object \admin\nonce
	 */
	private static $nonceInstance;

	/**
	 * @var object \mimosafa\Decoder
	 */
	private static $decoder;

	/**
	 *
	 */
	protected function __construct( $context ) {
		if ( ! $context || ! is_string( $context ) ) {
			return;
		}
		$this->context   = $context;
		$this->_post_new = ( 'add' === get_current_screen()->action ) ? true : false;
		self::$nonceInstance = new \WP_Domain_Work\WP\nonce( $context );
		self::$decoder       = new \mimosafa\Decoder();
	}

	public static function getInstance( $context ) {
		static $instance = null;
		$cl = __CLASS__;
		return $instance ?: new $cl( $context );
	}

	/**
	 *
	 */
	public function init( $post, $metabox ) {
		$dom_array = $this->generate_dom_array( $metabox['args'] );
		if ( empty( $dom_array ) ) {
			return;
		}
		$html  = self::$decoder->getArrayToHtmlString( $dom_array );
		$html .= self::$nonceInstance->nonce_field( $metabox['args']['name'] );
		echo $html;
	}

	/**
	 *
	 */
	private function generate_dom_array( $args ) {

		static $name  = '';
		static $id    = '';
		static $label = '';

		static $block  = [];
		static $inline = [];

		$type = $args['_type'];

		$return = [];

		if ( $type === 'group' ) {
			
			if ( ! array_key_exists( '_properties', $args ) || !  $args['_properties'] ) {
				return []; // error
			}
			if ( ! empty( $block ) ) {
				return; // error
			}
			$block = [
				'element'   => 'table',
				'attribute' => [ 'class' => 'form-table' ],
				'children'  => [
					[ 'element' => 'tbody', 'children' => [] ]
				]
			];
			$inner = [];
			foreach ( $args['_properties'] as $propArgs ) {
				$name .= $args['name'] . '[' . $propArgs['name'] . ']';
				$id   .= \WPDW_FORM_PREFIX . $args['name'] . '-' . $propArgs['name'];
				$_id   = $id;

				if ( $propDom = $this->generate_dom_array( $propArgs ) ) {
					$inner[] = [
						'element'  => 'tr',
						'children' => [
							[
								'element'  => 'th',
								'children' => [
									[
										'element'   => 'label',
										'attribute' => [ 'for' => esc_attr( $_id ) ],
										'text'      => esc_html( $propArgs['label'] )
									]
								]
							], [
								'element'  => 'td',
								'children' => $propDom
							]
						]
					];
				}
				$name = $id = '';
			}
			if ( ! empty( $inner ) ) {
				$block['children'][0]['children'] = $inner;
				$return[] = $block;
			}
			$block = [];

		} else if ( $type === 'set' ) {

			if ( !  array_key_exists( '_properties', $args ) || !  $args['_properties'] ) {
				return []; // error
			}
			if ( !  empty( $inline ) ) {
				return; // error
			}
			if ( '' === $name ) {
				$name .= $args['name'];
			}
			if ( '' === $id ) {
				$id .= \WPDW_FORM_PREFIX . $name;
			}
			$_name = $name;
			$_id   = $id;
			foreach ( $args['_properties'] as $propArgs ) {
				$name = $_name . '[' . $propArgs['name'] . ']';
				$id   = $_id . '-' . $propArgs['name'];
				$inline[] = $this->generate_dom_array( $propArgs )[0];
			}
			$return = $inline;
			$inline = [];

		} else if ( $type === 'post_children' ) {

			$table = new \WP_Domain_Work\Admin\list_table\Post_Children_List_Table( $args );
			$table->prepare_items();
			$table->display();

			/*
			if ( $args['value'] ) {
				echo '<pre>';
				var_dump( $args['value'] );
				echo '</pre>';
			}
			*/

		} else {

			/**
			 * 
			 */
			$required = (bool) $args['_required'];
			$multiple = (bool) $args['_multiple'];
			if ( $args['_readonly'] && $this->_post_new && array_key_exists( 'value', $args ) && $args['value'] ) {
				$readonly = true;
			} else {
				$readonly = false;
			}
			# $unique = (bool) $args['_unique'];

			if ( '' === $name ) {
				$name .= $args['name'];
			}
			if ( '' === $id ) {
				$id .= \WPDW_FORM_PREFIX . $name;
			}

			/**
			 *
			 */
			$dom  =  [ 'element'   => '', 'attribute' => [] ];
			$attr =& $dom['attribute'];

			if ( in_array( $type, [ 'string', 'integer', 'date', 'time' ] ) ) {

				/**
				 *
				 */
				$dom['element'] = 'input';
				$attr['id']     = esc_attr( $id );
				$attr['name']   = esc_attr( $name );

				if ( $required ) {
					$attr['required'] = 'required';
				}
				if ( $readonly ) {
					$attr['readonly'] = 'readonly';
				}
				if ( in_array( $type, [ 'string', 'integer' ] ) ) {

					if ( array_key_exists( 'value', $args ) ) {
						$attr['value'] = esc_attr( $args['value'] );
					}
					if ( 'string' === $type ) {

						$attr['type']  = 'text';
						$attr['class'] = 'regular-text';

					} else if ( 'integer' === $type ) {

						$attr['type']  = 'number';
						$attr['class'] = 'small-text';

					}

					$attr['placeholder'] = $args['label'];

				} else if ( 'date' === $type ) {

					if ( array_key_exists( 'value', $args ) ) {
						$attr['value'] = esc_attr( date( 'Y-m-d', strtotime( $args['value'] ) ) );
					}
					$attr['type'] = 'date';

				} else if ( 'time' === $type ) {

					if ( array_key_exists( 'value', $args ) ) {
						$attr['value'] = esc_attr( date( 'H:i', strtotime( $args['value'] ) ) );
					}
					$attr['type'] = 'time';

				}

			} else if ( 'select' === $type ) {

				/**
				 *
				 */
				$dom['element'] = 'select';
				$attr['id']     = esc_attr( $id );
				$attr['name']   = esc_attr( $name );

				if ( $required ) {
					$attr['required'] = 'required';
				}
				if ( $readonly ) {
					$attr['style'] = 'background-color:#eee;';
				}
				$dom['children'] = [];
				if ( ! $required ) {
					$dom['children'][] = [
						'element'   => 'option',
						'text'      => '-',
						'attribute' => [ 'value' => '' ]
					];
				}
				foreach ( $args['options'] as $key => $val ) {
					$child = [
						'element'   => 'option',
						'attribute' => [ 'value' => esc_attr( $key ) ],
						'text'      => esc_html( $val )
					];
					if ( array_key_exists( 'value', $args ) ) {
						if ( $key == $args['value'] ) {
							$child['attribute']['selected'] = 'selected';
						} else if ( $readonly ) {
							$child['attribute']['disabled'] = 'disabled';
						}
					}
					$dom['children'][] = $child;
				}

			} else if ( 'boolean' === $type ) {

				/**
				 *
				 */
				$_dom = [
					'element' => 'input',
					'attribute' => [
						'type'  => 'checkbox',
						'id'    => esc_attr( $id ),
						'name'  => esc_attr( $name ),
						'value' => 1, // $args['_true_value'],
					],
				];
				if ( array_key_exists( 'value', $args ) && $args['value'] ) {
					$_dom['attribute']['checked'] = 'checked';
				}

				if ( ! empty( $block ) && empty( $inline ) ) {
					$dom = $_dom;
				} else {
					$dom = [
						'element'   => 'label',
						'attribute' => [ 'for' => esc_attr( $id ), 'class' => \WPDW_FORM_PREFIX . 'checkbox' ],
						'children'  => [ $_dom ],
						'text'      => esc_html( $args['label'] ),
					];
				}


			}

			if ( ! empty( $block ) || ! empty( $inline ) ) {
				if (
					( array_key_exists( 'prefix', $args ) && is_string( $args['prefix'] ) && ( $prefix = $args['prefix'] ) )
					|| ( array_key_exists( 'safix', $args ) && is_string( $args['safix'] ) && ( $safix = $args['safix'] ) )
				) {
					$_dom = [ 'element' => 'label', 'children' => [], 'attribute' => [ 'class' => \WPDW_FORM_PREFIX . 'label', 'for' => esc_attr( $id ) ], ];
					if ( isset( $prefix ) && $prefix ) {
						$_dom['children'][] = [ 'element' => 'span', 'text' => $prefix ];
					}
					$_dom['children'][] = $dom;
					if ( isset( $safix ) && $safix ) {
						$_dom['children'][] = [ 'element' => 'span', 'text' => $safix ];
					}
					$return[] = $_dom;
				} else {
					$return[] = $dom;
				}
			} else {
				$return[] = [ 'element' => 'p', 'children' => [ $dom ] ];
			}

		}

		if ( ! in_array( $type, [ 'group', 'set' ] ) ) {
			/**
			 * Initialize static property $name, $id.
			 */
			$name = $id = '';
		}

		/**
		 *
		 */
		if ( array_key_exists( 'description', $args ) && ! empty( $args['description'] ) ) {
			$desc = [
				'element' => 'p',
				'text'    => esc_html( $args['description'] ),
			];
			if ( 'group' === $type || 'post_children' === $type ) {
				array_unshift( $return, $desc );
			} else {
				$desc['attribute'] = [ 'class' => 'description' ];
				$return[] = $desc;
			}
		}

		return $return;
	}

}
