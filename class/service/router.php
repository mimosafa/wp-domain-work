<?php

namespace WP_Domain_Work\Service;

/**
 *
 */
class Router {

	/**
	 * Hierarchical level WordPress path
	 *
	 * @see wp-domain-work/class/wp-domain-work.php
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
		'status',
		'query',    // Name of class, who controls 'main query' & public or private in frontend.
		'template', // Name of class, who controls 'template'.
	];

	/**
	 * @uses WP_Domain_Work
	 */
	public function __construct() {
		/**
		 * Get instance plugin class
		 */
		$_WPDW = \WP_Domain_Work\Plugin::getInstance();

		if ( ! is_admin() ) {
			$this->_level = $_WPDW::get_home_level();
		} else {
			$this->_level = $_WPDW::get_site_level();
			$this->_is_admin = true;
		}
		$this->decomposeUri();
		$this->dispatch();
		if ($this->_hook !== '') {
			$this->init();
		}
	}

	/**
	 * URIを分解する
	 */
	private function decomposeUri() {
		if ( $this->decomposeRequestUri() ) {
			$this->decomposeQueryString();
		}
	}

	/**
	 * URIを pathと query stringに分け、 pathを更に分解。最後に query stringの有無を返す。
	 */
	private function decomposeRequestUri() {
		$uri = explode('?', $_SERVER['REQUEST_URI']);
		$path = trim($uri[0], '/');
		if ($path) {
			$strings = explode('/', $path);
			for ( $i = $this->_level; $i < count($strings); $i++ ) {
				$this->_path[] = $strings[$i];
			}
		}
		return isset($uri[1]);
	}

	/**
	 * Query stringの分解
	 */
	private function decomposeQueryString() {
		if (!$q_str = $_SERVER['QUERY_STRING']) {
			return;
		}
		$q_arr = explode('&', $q_str);
		foreach ($q_arr as $str) {
			$q = explode('=', $str);
			$this->_query_args[$q[0]] = isset( $q[1] ) ? $q[1] : true;
		}
	}

	/**
	 * Define 'namespace' & 'action hook'
	 */
	private function dispatch() {
		if ( empty( $this->_path ) ) {

			// home

		} else {

			/**
			 * $_pathの0番目要素を取り出す
			 */
			$topPath = array_shift( $this->_path );

			if ( 'wp-admin' === $topPath ) {

				/**
				 * wp-admin
				 */
				$this->adminDispatch();

			} else if ( $topPath ) {

				/**
				 * frontend
				 */
				$this->_ns = $topPath;

			}

		}
		if ( '' === $this->_hook ) {
			$this->_hook = 'wp_loaded';
		}
	}

	/**
	 * Define 'namespace' & 'action hook' in admin
	 */
	private function adminDispatch() {
		global $pagenow;
		switch ( $pagenow ) {
			// posts & post
			case 'edit.php' :
			case 'post-new.php' :
				if ( isset( $this->_query_args['post_type'] ) ) {
					$this->_ns = $this->_query_args['post_type'];
				}
				$this->_hook = 'admin_init';
				break;
			case 'post.php' :
				add_action( 'current_screen', function() {
					$screen = get_current_screen();
					$post_type = $screen->post_type;
					$this->_ns = $post_type;
				} );
				break;
			// tax
			case 'edit-tags.php' :
				if ( isset( $this->_query_args['taxonomy'] ) ) {
					$this->_ns = $this->_query_args['taxonomy'];
				}
				break;
			//
			default :
				//_var_dump( $ns );
				break;
		}
		if ( '' === $this->_hook ) {
			$this->_hook = "load-{$pagenow}";
		}
	}

	/**
	 *
	 */
	private function init() {
		/**
		 * Admin dashboard
		 */
		if ( true === $this->_is_admin ) {
			/**
			 * Initialize common admin setting. (autoload '\service\admin_init')
			 */
			//new admin_init();

			/**
			 * Initialize admin setting, referenced by domain.
			 */
			add_action( $this->_hook, [ $this, 'init_admin' ] );
		}
		/**
		 * Initialize services, referenced by domain.
		 */
		add_action( $this->_hook, [ $this, 'init_services'] );

		add_filter( 'template_include', [ $this, 'template_include' ] );
	}

	/**
	 *
	 */
	public function init_admin() {
		$this->construct( 'admin' );
	}

	/**
	 *
	 */
	public function init_services() {
		foreach ( self::$_services as $class ) {
			$this->construct( $class );
		}
	}

	/**
	 *
	 */
	private function construct( $class ) {
		if ( !$class || is_null( $this->_ns ) ) {
			return;
		}
		/**
		 * 管理画面では、投稿タイプ名、タクソノミー名とスラッグが異なる場合もあるため
		 */
		if ( true === $this->_is_admin ) {
			if ( $obj = get_post_type_object( $this->_ns ) ) {
				$this->_ns = $obj->rewrite['slug'];
			} elseif ( $obj = get_taxonomy( $this->_ns ) ) {
				$this->_ns = $obj->rewrite['slug'];
			}
			$this->_is_admin = '...But, namespace is already checked! :)';
		}
		$cl = sprintf( 'WP_Domain\\%s\\%s', $this->_ns, $class );
		if ( class_exists( $cl ) ) {
			static $args = null;
			if ( ! $args ) {
				$args = array_merge( [ 'path' => $this->_path ], $this->_query_args );
			}
			$cl::init( $args );
		}
	}

	/**
	 * @see https://github.com/WordPress/WordPress/blob/4.1-branch/wp-includes/template-loader.php#L73
	 */
	public function template_include( $template ) {
		if ( is_null( $this->_ns ) ) {
			return $template;
		}

		global $post_type;
		$filenow = substr( $template, strripos( $template, '/' ) + 1 );
		$is = is_archive() ? 'archive' : 'single';
		if ( $filenow === "{$is}-{$post_type}.php" ) {
			return $template;
		}

		$domains_dirs = \WP_Domain_Work\Plugin::get_domains_dirs();
		$dirs = array_map( function( $var ) {
			return sprintf( '%s/%s/', Domains::add_path_prefix( $var ), $this->_ns );
		}, $domains_dirs );

		foreach ( [ "{$is}.php", 'index.php' ] as $file ) {
			foreach ( $dirs as $dir ) {
				$path = $dir . $file;
				if ( is_readable( $path ) ) {
					return $path;
				}
			}
		}
		return $template;
	}

}
