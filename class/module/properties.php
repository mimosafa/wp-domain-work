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
		'post_parent' => [
			'model' => 'post_parent',
			'type'  => 'post',
		],
	];

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
	}

	/**
	 * Get defined properties setting
	 *
	 * @access public
	 *
	 * @param  string|null $prop (optional) property name, if null value, get all settings.
	 * @return array
	 */
	public function get_property_setting( $prop = null ) {
		if ( ! isset( $this->properties ) || ! is_array( $this->properties ) || ! $this->properties ) {
			return null;
		}
		if ( null === $prop ) {
			return $this->properties;
		}
		return array_key_exists( $prop, $this->properties ) ? $this->properties[$prop] : null;
	}

	/**
	 * Overloading method, '__isset'
	 * Check property definition, and set property instance, if defined.
	 * e.g. isset( $this -> var )
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
		if ( ! $propSetting = $this->get_property_setting( $var ) ) {
			return false;
		}
		// insert conditional logic here ?

		if ( in_array( $var, [ 'post_parent', 'menu_order' ] ) ) {
			$instance = new \stdClass();
			$instance->$var = $this->_post->$var;
			foreach ( $propSetting as $key => $val ) {
				$instance->$key = $val;
			}
			$this->_data[$var] = $instance;
		} else if ( array_key_exists( 'model', $propSetting ) ) {
			$modelName = $propSetting['model'];
			if ( ! $_Model =& $this->_get_model( $modelName ) ) {
				return false;
			}
			$propSetting = array_merge( self::$defaultPropSettings[$modelName], $propSetting );
			$typeClass = '\\property\\' . $propSetting['type'];
			if ( !class_exists( $typeClass ) ) {
				return false;
			}
			$instance = new $typeClass( $var, $propSetting );
			$instance->val( $_Model->get( $var ) );
			$this->_data[$var] = $instance;
		} else if ( 'post_children' === $propSetting['type'] ) {

			$instance = new \property\post_children( $var, $propSetting );

			if ( null === self::$models['posts'] ) {
				self::$models['posts'] = new \wordpress\model\posts();
			}
			$model =& self::$models['posts'];
			$queryArgs = $instance -> getQueryArgs();

			$instance -> value = $model -> get( $queryArgs );

			$this -> _data[$var] = $instance;

		} else if ( 'post_parent' === $propSetting['type'] ) {

			$instance = new \property\post_parent( $var, $propSetting );

			$parent_id = $this -> _post -> post_parent;
			$instance -> value = $parent_id ? intval( $parent_id ) : null;

			$this -> _data[$var] = $instance;

		} else if ( 'group' === $propSetting['type'] ) {

			/**
			 * Grouped property
			 */
			if ( !array_key_exists( 'elements', $propSetting ) || !\utility\is_vector( $propSetting['elements'] ) ) {
				return false;
			}
			$instance = new \property\group( $var, $propSetting );

			foreach ( $propSetting['elements'] as $element ) {
				if ( $elementData = $this -> $element ) {
					$instance -> set_element( $element, $elementData );
				}
			}

			if ( empty( $instance -> properties ) ) {
				return false;
			}

			$this -> _data[$var] = $instance;

		}

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
		return isset( $this -> $var ) ? $this -> _data[$var] : null;
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

		/**
		 * Check capability
		 */
		if ( !current_user_can( 'edit_post', $this -> _post -> ID ) ) {
			return;
		}

		/**
		 * Get property instance
		 */
		$property = $this -> $name;

		if ( is_null( $property ) ) {
			return;
		}

		$type = \utility\getEndOfClassname( $property );

		if ( 'group' === $type ) {

			if ( !is_array( $value ) ) {
				return;
			}

			$elements = array_keys( $property -> properties );
			foreach ( $elements as $element ) {
				$newValue = array_key_exists( $element, $value )
					? $value[$element]
					: ''
				;
				$this -> $element = $newValue;
			}

		} else if ( 'post_children' === $type ) {

			//

		} else {

			$modelStr = $property -> getModel();
			$model =& self::$models[$modelStr];

			$newValue = $property -> filter( $value );

			if ( null === $newValue ) {

				unset( $model -> $name );

			} else if ( $newValue !== $property -> value ) {

				$model -> $name = $newValue;
				unset( $this -> _data[$name] );

			}

		}

		return isset( $name );

	}

	/**
	 * @access private
	 * 
	 * @param  string $modelName
	 * @return reference
	 */
	private function &_get_model( $modelName ) {
		if ( array_key_exists( $modelName, self::$models ) ) {
			return self::$models[$modelName];
		}
		$modelClass = "\\wordpress\\model\\{$modelName}";
		if ( ! class_exists( $modelClass ) ) {
			return false;
		}
		self::$models[$modelName] = new $modelClass( $this->_post );
		return self::$models[$modelName];
	}

}
