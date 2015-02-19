<?php

/**
 * Class loader
 *
 * @see http://sotarok.hatenablog.com/entry/20101208/1291739722
 * @see https://gist.github.com/sotarok/732010
 */
class ClassLoader {

	/**
	 * Defined flags
	 *
	 *  0. (default)
	 *     - ex: 'Model_Class' -> 'Model_Class.php'
	 *  1. FILENAME_STRTOLOWER
	 *     - Class name string to lower case for file name
	 *     - ex: 'Model_Class' -> 'model_class.php'
	 *  2. NAMESPACE_STRTOLOWER
	 *     - Namespace string to lower case for directory name
	 *     - ex: 'Name_Space\Model_Class' -> 'name_space/Model_Class.php'
	 *  4. FILENAME_UNDERBAR_AS_HYPHEN
	 *     - '_' in class name replace to '-'
	 *     - ex: 'Model_Class' -> 'Model-Class.php'
	 *  8. FILENAME_UNDERBAR_AS_DIR_SEP
	 *     - '_' in class name replace to '/' for directory separator
	 *     - ex: 'Model_Class' -> 'Model/Class.php'
	 * 16. NAMESPACE_UNDERBAR_AS_DIR_SEP
	 *     - '_' in namespace replace to '-'
	 *     - ex: 'Name_Space\Model_Class' -> 'Name-Space/Model_Class.php'
	 * 32. NAMESPACE_UNDERBAR_AS_DIR_SEP
	 *     - '_' in namespace replace to '/' for directory separator
	 *     - ex: 'Name_Space\Model_Class' -> 'Name/Space/Model_Class.php'
	 * 64. REMOVE_FIRST_NAMESPACE_STRING
	 *     - First string of namespace will be removed
	 *     - ex: 'Name\Space\Model_Class' -> 'Space/Model_Class.php'
	 *
	 * (Bitwise operation use as $flag
	 *  -> http://qiita.com/mpyw/items/ce626976ec4dc07dfec2#1-2)
	 */
	const FILENAME_STRTOLOWER           = 1;
	const NAMESPACE_STRTOLOWER          = 2;
	const FILENAME_UNDERBAR_AS_HYPHEN   = 4;
	const FILENAME_UNDERBAR_AS_DIR_SEP  = 8;
	const NAMESPACE_UNDERBAR_AS_HYPHEN  = 16;
	const NAMESPACE_UNDERBAR_AS_DIR_SEP = 32;
	const REMOVE_FIRST_NAMESPACE_STRING = 64;

	/**
	 * Supplied flag
	 *
	 * @var int
	 */
	private $flag = 0;

	/**
	 * Stocker of registered autoloader
	 *
	 * @var array
	 */
	private static $_loaders = [];

	/**
	 * @var string
	 */
	private $_namespace;

	/**
	 * @var string
	 */
	private $_includePath;

	/**
	 * @var string
	 */
	private $_namespace_separator = '\\';

	private $_cacheGroup = 'wp-domain-work-classloader';

	/**
	 * @access private
	 *
	 * @param  string|null $ns
	 * @param  string|null $path
	 * @param  int $flag
	 * @return (void)
	 */
	private function __construct( $ns, $path, $flag = null ) {
		$this->_namespace = $ns;
		$this->_includePath = realpath( $path );

		/**
		 *
		 */
		if ( $flag && is_int( $flag ) ) {
			$this->flag = $flag;
		}
	}

	/**
	 * @access private
	 */
	private function _register() {
		spl_autoload_register( [ $this, 'loadClass' ] );
	}

	/**
	 * @access private
	 */
	private function _unregister() {
		spl_autoload_unregister( [ $this, 'loadClass' ] );
	}

	/**
	 * @access public
	 *
	 * @param  string $className
	 */
	public function loadClass( $className ) {
		$sep = $this->_namespace_separator;
		if (
			! $this->_namespace
			|| $this->_namespace . $sep === substr( $className, 0, strlen( $this->_namespace . $sep ) )
		) {
			// WordPress Object Cache API - get
			if ( ! $fileName = wp_cache_get( $className, $this->_cacheGroup ) ) {
				$fileName  = '';
				$namespace = '';
				if ( false !== ( $lastNsPos = strripos( $className, $sep ) ) ) {
					if (
						self::REMOVE_FIRST_NAMESPACE_STRING & $this->flag
						&& false !== ( $firstNsPos = strpos( $className, $sep ) )
					) {
						$className = substr( $className, $firstNsPos + 1 );
						$lastNsPos = $lastNsPos - $firstNsPos - 1;
					}
					if ( $lastNsPos > 0 ) {
						$namespace = substr( $className, 0, $lastNsPos );
						$className = substr( $className, $lastNsPos + 1 );

						if ( self::NAMESPACE_UNDERBAR_AS_HYPHEN & $this->flag ) {
							$namespace = str_replace( '_', '-', $namespace );
						} else if ( self::NAMESPACE_UNDERBAR_AS_DIR_SEP & $this->flag ) {
							$namespace = str_replace( '_', '/', $namespace );
						}

						$fileName = str_replace( $sep, '/', $namespace ) . '/';

						if ( self::NAMESPACE_STRTOLOWER & $this->flag ) {
							$fileName = strtolower( $fileName );
						}
					}
				}

				/**
				 * Analys flag 'FILENAME_STRTOLOWER'
				 */
				if ( self::FILENAME_STRTOLOWER & $this->flag ) {
					$className = strtolower( $className );
				}

				/**
				 * Analys flag 'FILENAME_UNDERBAR_AS_HYPHEN' or 'FILENAME_UNDERBAR_AS_DIR_SEP'
				 */
				if ( self::FILENAME_UNDERBAR_AS_HYPHEN & $this->flag ) {
					$replace = '-';
				} else if ( self::FILENAME_UNDERBAR_AS_DIR_SEP & $this->flag ) {
					$replace = '/';
				}

				if ( isset( $replace ) ) {
					$fileName .= str_replace( '_', $replace, $className );
				} else {
					$fileName .= $className;
				}
				$fileName .= '.php';

				// WordPress Object Cache API - add
				wp_cache_add( $className, $fileName, $this->_cacheGroup );
			}
			$filePath = $this->_includePath . '/' . $fileName;

			if ( file_exists( $filePath ) ) {
				require $filePath;
			}
		}
	}

	/**
	 * @access public
	 */
	public static function register( $ns = '', $path = null, $flag = 0 ) {
		$cl = new self( $ns, $path, $flag );
		$cl -> _register();
		$key = $ns . $path . $flag;
		self::$_loaders[ $key ] = $cl;
	}

	/**
	 * @access public
	 */
	public static function unregister( $ns = '', $path = null, $flag = 0 ) {
		$key = $ns . $path . $flag;
		if ( isset( $key ) ) {
			self::$_loaders[ $key ] -> _unregister();
		}
	}

}
