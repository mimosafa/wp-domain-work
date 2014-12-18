<?php

namespace service;

/**
 *
 */
class router {

	/**
	 * Hierarchical level that WordPress is installed
	 * (This is stored in option table as 'wordpress_installed_hierarchy_lebel_on_server')
	 * 0 if installed server under root
	 *
	 * @var int
	 */
	private $_level;

	/**
	 * Parameters path of uri
	 *
	 * @var array
	 */
	private $_path = [];

	/**
	 * Parameters query arguments of uri
	 *
	 * @var array
	 */
	private $_query_args = [];

	/**
	 * @var bool
	 */
	private $_is_admin = false;

	/**
	 * Namespace
	 *
	 * @var string
	 */
	private $_ns;

	/**
	 * Action hook
	 *
	 * @var string
	 */
	private $_hook = '';

	/**
	 * @var array
	 */
	private static $_services = [
		'query',    // Name of class, who controls 'main query' & public or private in frontend.
		'template', // Name of class, who controls 'template'.
	];

	/**
	 *
	 */
	public function __construct() {
		$this -> _level = get_option( 'wp_dct_wordpress_installed_hierarchy_lebel_on_server' );
		$this -> decomposeUri();
		$this -> dispatch();
		if ( $this -> _hook !== '' ) {
			$this -> init();
		}
	}

	/**
	 * URIを分解する
	 */
	private function decomposeUri() {
		if ( $this -> decomposeRequestUri() ) {
			$this -> decomposeQueryString();
		}
	}

	/**
	 * URIを pathと query stringに分け、 pathを更に分解。最後に query stringの有無を返す。
	 */
	private function decomposeRequestUri() {
		$uri = explode( '?', $_SERVER['REQUEST_URI'] );
		$path = trim( $uri[0], '/' );
		if ( $path ) {
			$strings = explode( '/', $path );
			for ( $i = $this -> _level; $i < count( $strings ); $i++ ) {
				$this -> _path[] = $strings[$i];
			}
		}
		return isset( $uri[1] );
	}

	/**
	 * Query stringの分解
	 */
	private function decomposeQueryString() {
		if ( !$q_str = $_SERVER['QUERY_STRING'] )
			return;
		$q_arr = explode( '&', $q_str );
		foreach ( $q_arr as $str ) {
			$q = explode( '=', $str );
			$this -> _query_args[$q[0]] = isset( $q[1] ) ? $q[1] : true;
		}
	}

	/**
	 * Define 'namespace' & 'action hook'
	 */
	private function dispatch() {
		if ( empty( $this -> _path ) ) {

			// home

		} else {

			/**
			 * $_pathの0番目要素を取り出す
			 */
			$topPath = array_shift( $this -> _path );

			if ( 'wp-admin' === $topPath ) {

				/**
				 * wp-admin
				 */
				$this -> _is_admin = true;
				$this -> adminDispatch();

			} else if ( $topPath ) {

				/**
				 * frontend
				 */
				$this -> _ns = $topPath;

			}

		}
		if ( '' === $this -> _hook ) {
			$this -> _hook = 'wp_loaded';
		}
	}

	/**
	 * Define 'namespace' & 'action hook' in admin
	 */
	private function adminDispatch() {
		$string = !empty( $this -> _path ) ? $this -> _path[0]: 'index.php';
		switch ( $string ) {
			// posts & post
			case 'edit.php' :
			case 'post-new.php' :
				if ( isset( $this -> _query_args['post_type'] ) ) {
					$this -> _ns = $this -> _query_args['post_type'];
				}
				$this -> _hook = 'admin_init';
				break;
			case 'post.php' :
				add_action( 'current_screen', function() {
					$screen = get_current_screen();
					$post_type = $screen -> post_type;
					$this -> _ns = $post_type;
				} );
				break;
			// tax
			case 'edit-tags.php' :
				if ( isset( $this -> _query_args['taxonomy'] ) ) {
					$this -> _ns = $this -> _query_args['taxonomy'];
				}
				break;
			//
			default :
				//_var_dump( $ns );
				break;
		}
		if ( '' === $this -> _hook )
			$this -> _hook = 'load-' . $string;
	}

	/**
	 *
	 */
	private function init() {
		/**
		 * Admin dashboard
		 */
		if ( true === $this -> _is_admin ) {

			/**
			 * Initialize common admin setting. (autoload '\service\admin_init')
			 */
			//new admin_init();

			/**
			 * Initialize admin setting, referenced by domain.
			 */
			add_action( $this -> _hook, [ $this, 'init_admin' ] );

		}

		/**
		 * Initialize services, referenced by domain.
		 */
		add_action( $this -> _hook, [ $this, 'init_services'] );
	}

	/**
	 *
	 */
	public function init_admin() {
		$this -> construct( 'admin' );
	}

	/**
	 *
	 */
	public function init_services() {
		foreach ( self::$_services as $class ) {
			$this -> construct( $class );
		}
	}

	/**
	 *
	 */
	private function construct( $class ) {
		if ( !$class || is_null( $this -> _ns ) ) {
			return;
		}

		/**
		 * 管理画面では、投稿タイプ名、タクソノミー名とスラッグが異なる場合もあるため
		 */
		if ( true === $this -> _is_admin ) {
			if ( $obj = get_post_type_object( $this -> _ns ) ) {
				$this -> _ns = $obj -> rewrite['slug'];
			} elseif ( $obj = get_taxonomy( $this -> _ns ) ) {
				$this -> _ns = $obj -> rewrite['slug'];
			}
			$this -> _is_admin = '...But, namespace is already checked! :)';
		}

		/**
		 * Create string '\namespace\class'.
		 */
		$cl = '\\' . $this -> _ns . '\\' . $class;

		/**
		 * Construct Class. (autoload)
		 */
		if ( class_exists( $cl ) ) {
			new $cl();
		}
	}

}
