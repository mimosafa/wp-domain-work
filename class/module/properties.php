<?php

namespace WP_Domain_Work\Module;

use \WP_Domain_Work\Utility as Util;

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
	 * @var string
	 */
	private $domain;

	/**
	 * @var array
	 */
	private $_data = [];

	/**
	 * @var array WP_Domain_Work\WP\model\~
	 */
	private static $models = [
		// 'metadata' => null,
		// 'taxonomy' => null,
	];

	/**
	 *
	 */
	public static $defaultPropSettings = [
		'metadata' => [ 'type' => 'string', 'multi_byte' => true, ],
		'taxonomy' => [ 'type' => 'term', ],
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
		 * Define domain's name by namespace string
		 * @uses WP_Domain_Work\Utility\String_Function::getNamespace
		 */
		$domainNS = Util\String_Function::getNamespace( $this );
		$this->domain = substr( $domainNS, strripos( $domainNS, '\\' ) + 1 );
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
		if ( ! $prop ) {
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
		/**
		 * Define type name by classname string
		 * @uses \WP_Domain_Work\Utility\classname::getClassname
		 */
		$type = Util\String_Function::getClassname( $property );

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
			//var_dump( $value ); die();

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
			$typeClass = "WP_Domain_Work\\Property\\{$name}";
			$instance = new $typeClass( $this->_post, (array) $args ); // (array)... default で良い場合は 1 とか入れる場合もあるので
		} else if ( array_key_exists( 'model', $args ) ) {
			$modelName = $args['model'];
			if ( ! $_Model =& $this->_get_model( $modelName, $this->_post->ID ) ) {
				return;
			}
			$args = array_merge( self::$defaultPropSettings[$modelName], $args );
			$typeClass = 'WP_Domain_Work\\Property\\' . $args['type'];
			if ( class_exists( $typeClass ) ) {
				$instance = new $typeClass( $name, $args );
				$instance->val( $_Model->get( $name ) );
			}
		} else if ( 'post_children' === $args['type'] ) {
			$instance = new \WP_Domain_Work\Property\post_children( $name, $args, $this->_post );
		} else if ( in_array( $args['type'], [ 'group', 'set' ] ) ) {
			/**
			 * Idiom: $array !== array_values( $array )
			 *        - Check $array is associative array or not
			 *
			 * @see http://qiita.com/Hiraku/items/721cc3a385cb2d7daebd
			 */
			if ( ! array_key_exists( 'elements', $args ) || $args['elements'] !== array_values( $args['elements']) ) {
				return;
			}
			$typeClass = 'WP_Domain_Work\\Property\\' . $args['type'];
			if ( class_exists( $typeClass ) ) {
				$instance = new $typeClass( $name, $args );
				foreach ( $args['elements'] as $element ) {
					if ( $elementData = $this->$element ) {
						$instance->set_element( $element, $elementData );
					}
				}
				if ( empty( $instance->properties ) ) {
					return;
				}
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
	}

	/**
	 * 無駄にモデルインスタンスをコンストラクトしない用の関数
	 * 1) 問題でイマイチいけていない関数になっている気がする… orz
	 *
	 * @access private
	 * 
	 * @param  string $modelName
	 * @return reference
	 */
	private function &_get_model( $modelName, $post_id ) {
		/**
		 * 1) アーカイブや wp-admin での一覧表示の際に model が上書きされない不具合発生。
		 * $_post_id をフラグとして引数に追加。
		 */
		static $_post_id = 0;
		if ( $post_id !== $_post_id ) {
			self::$models = [];
		}
		if ( ! array_key_exists( $modelName, self::$models ) ) {
			$modelClass = "WP_Domain_Work\\WP\\model\\{$modelName}";
			if ( ! class_exists( $modelClass ) ) {
				return self::$falseVal;
			}
			self::$models[$modelName] = new $modelClass( $this->_post );
			/**
			 * フラグとして $_post_id を上書き
			 */
			$_post_id = $post_id;
		}
		return self::$models[$modelName];
	}

}
