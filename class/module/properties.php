<?php

namespace module;

/**
 * This is 'Trait',
 * must be used in '\(domain)\properties' class.
 *
 * @uses 
 */
trait properties {

	/**
	 * @var WP_Post
	 */
	private $_post;

	private static $post_id;

	private $domain;

	/**
	 * @var array
	 */
	private $_data = [];

	/**
	 * @var array \wordpress\model\~ instance
	 */
	private static $models = [];

	/**
	 *
	 */
	private static $defaultPropSettings = [
		'metadata' => [
			'type'       => 'string',
			'multi_byte' => true,
		],
	];

	private static $falseVal = false;

	/**
	 * Constructor
	 *
	 * @access public
	 *
	 * @param  WP_Post|int $post
	 * @return (void)
	 */
	public function __construct( $post = 0 ) {
		if ( ! $post = get_post( $post ) ) {
			return null;
		}
		$this->_post = $post;
		/**
		 * define domain's name
		 *
		 * @uses \utility\getObjectNamespace
		 */
		$this->domain = \utility\getObjectNamespace( $this );
	}

	/**
	 * Get defined properties setting
	 *
	 * @access public
	 *
	 * @param  string|null $prop (optional) property name, if null value, get all settings.
	 * @return array
	 */
	public static function get_property_setting( $prop = null ) {
		if ( ! isset( self::$properties ) || ! is_array( self::$properties ) || ! self::$properties ) {
			return null;
		}
		if ( null === $prop ) {
			return self::$properties;
		}
		return array_key_exists( $prop, self::$properties ) ? self::$properties[$prop] : null;
	}

	/**
	 * Overloading method, '__isset'
	 * Check property definition, and set property instance, if defined.
	 * e.g. isset( $this->var )
	 *
	 * @access public
	 *
	 * @todo   insert conditional logic
	 *
	 * @param  string $var (required) name of property.
	 * @return bool
	 */
	public function __isset( $var ) {
		if ( array_key_exists( $var, $this->_data ) ) {
			return true;
		}
		if ( ! $propSetting = self::get_property_setting( $var ) ) {
			return false;
		}
		$this->_set_property( $var, $propSetting );
		return array_key_exists( $var, $this->_data );
	}

	/**
	 * Overloading method, '__get'
	 * Get property instance, if exists.
	 *
	 * @access public
	 *
	 * @param  string $var (required) property name
	 * @return null|object
	 */
	public function __get( $var ) {
		return isset( $this->$var ) ? $this->_data[$var] : null;
	}

	/**
	 * Overloading method, '__set'.
	 * Edit property value, using model instnce.
	 *
	 * @uses
	 *
	 * @param string $name (required)
	 * @param mixed $value (required) new value. if null value, delete property's value.
	 */
	public function __set( $name, $value ) {
		if ( ! $property = $this->$name  ) {
			return;
		}
		$type = \utility\getEndOfClassname( $property );

		if ( 'group' === $type || 'set' === $type ) {
			if ( !is_array( $value ) ) {
				return;
			}
			$elements = array_keys( $property->properties );
			foreach ( $elements as $element ) {
				$newValue = array_key_exists( $element, $value ) ? $value[$element] : '';
				$this->$element = $newValue;
			}
		} else if ( 'post_children' === $type ) {

			//

		} else {
			$modelName = $property->getModel();
			$model =& $this->_get_model( $modelName, $this->_post->ID );
			$newValue = $property->filter( $value );
			if ( null === $newValue ) {
				unset( $model->$name );
			} else if ( $newValue !== $property->value ) {
				$model->$name = $newValue;
				unset( $this->_data[$name] );
			}
		}
		return isset( $this->$name );
	}

	/**
	 * @access private
	 *
	 * @param  string $name
	 * @param  array  $args
	 */
	private function _set_property( $name, $args ) {
		$instance = null;
		if ( in_array( $name, [ 'post_parent', 'menu_order' ] ) ) {
			/**
			 * Post's default attributes
			 */
			$typeClass = "\\property\\{$name}";
			$instance = new $typeClass( $this->_post, (array) $args ); // (array)... default で良い場合は 1 とか入れる場合もあるので
		} else if ( array_key_exists( 'model', $args ) ) {
			$modelName = $args['model'];
			if ( ! $_Model =& $this->_get_model( $modelName, $this->_post->ID ) ) {
				return false;
			}
			$args = array_merge( self::$defaultPropSettings[$modelName], $args );
			$typeClass = '\\property\\' . $args['type'];
			if ( !class_exists( $typeClass ) ) {
				return false;
			}
			$instance = new $typeClass( $name, $args );
			$instance->val( $_Model->get( $name ) );
		} else if ( 'post_children' === $args['type'] ) {
			$instance = new \property\post_children( $name, $args, $this->_post );
		} else if ( in_array( $args['type'], [ 'group', 'set' ] ) ) {
			/**
			 * Grouped property
			 */
			if ( !array_key_exists( 'elements', $args ) || !\utility\is_vector( $args['elements'] ) ) {
				return false;
			}
			$typeClass = '\\property\\' . $args['type'];
			if ( !class_exists( $typeClass ) ) {
				return false;
			}
			$instance = new $typeClass( $name, $args );
			foreach ( $args['elements'] as $element ) {
				if ( $elementData = $this->$element ) {
					$instance->set_element( $element, $elementData );
				}
			}
			if ( empty( $instance->properties ) ) {
				return false;
			}
		}
		/**
		 * 各プロパティの出力関数(getValue)で、各プロパティ固有のフィルターフックを追加させる際に、異なるドメインで同一のプロパティ名が
		 * 存在した場合に不具合が発生しないように、フィルター名に追加するための domain 名を引数に追加した。
		 */
		if ( $instance ) {
			$instance->domain = $this->domain;
			$this->_data[$name] = $instance;
		}
		return array_key_exists( $name, $this->_data );
	}

	/**
	 * @access private
	 * 
	 * @param  string $modelName
	 * @return reference
	 */
	private function &_get_model( $modelName, $post_id ) {
		if ( array_key_exists( $modelName, self::$models ) && $post_id === self::$post_id ) {
			/**
			 * アーカイブや wp-admin での一覧表示の際に model が上書きされない不具合発生。
			 * $post_id をフラグとして引数に追加した。
			 */
			return self::$models[$modelName];
		}
		$modelClass = "\\wordpress\\model\\{$modelName}";
		if ( ! class_exists( $modelClass ) ) {
			return self::$falseVal;
		}
		self::$models[$modelName] = new $modelClass( $this->_post );
		/**
		 * フラグとして ID を上書き
		 */
		self::$post_id = $post_id;
		return self::$models[$modelName];
	}

}
