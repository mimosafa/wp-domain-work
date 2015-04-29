<?php
namespace WPDW;

/**
 * @uses   WPDW\Options
 * @global $wp, $pagenow
 */
class Router {
	use Util\Singleton { getInstance as init; }

	/**
	 * Domain name
	 * @var string
	 */
	private $ns;

	/**
	 * @var array
	 */
	private $arguments = [];

	/**
	 * @var array
	 */
	private $domains_alias;

	/**
	 * @var array
	 */
	private $services = [
		/* 'admin', */
		'query'
	];

	/**
	 * Input vars definition. use in admin.
	 * @var array
	 */
	private static $def = [
		'post_type' => \FILTER_SANITIZE_ENCODED,
		'taxonomy'  => \FILTER_SANITIZE_ENCODED,
		'post'      => \FILTER_VALIDATE_INT,
		// and more
	];

	/**
	 * Constructor
	 * @access protected
	 * @uses   WPDW\Options
	 * @return (void)
	 */
	protected function __construct() {
		$this->domains_alias = Options::get_domains_alias();
		! is_admin() ?  $this->template_redirect() : $this->admin_init();
	}

	/**
	 * Frontend hook
	 * @access private
	 * @return (void)
	 */
	private function template_redirect() {
		add_action( 'template_redirect', [ $this, 'parse_request' ], 0 );
		add_action( 'template_redirect', [ $this, 'init_service' ], 1 );
	}

	/**
	 * @access public
	 * @return (void)
	 */
	public function parse_request() {
		global $wp;
		if ( $wp->did_permalink ) {
			$path = explode( '/', $wp->request );
			$topPath = array_shift( $path );
			if ( $topPath && in_array( $topPath, $this->domains_alias, true ) ) {
				$this->ns = $topPath;
				$this->arguments = $wp->query_vars + [ 'domain' => $this->ns ];
			}
		} else {
			$q = $wp->query_vars;
			if ( isset( $q['post_type'] ) && isset( $this->domains_alias[$q['post_type']] ) ) {
				$this->ns = $this->domains_alias[$q['post_type']];
			} else {
				$excluded = $wp->public_query_vars + $wp->private_query_vars;
				foreach ( $q as $key => $val ) {
					if ( array_key_exists( $key, $this->domains_alias ) ) {
						$this->ns = $this->domains_alias[$key];
						break;
					}
				}
			}
			if ( $this->ns )
				$this->arguments = $q + [ 'domain' => $this->ns ];
		}
	}

	/**
	 * Admin hook
	 * @access private
	 * @return (void)
	 */
	private function admin_init() {
		$this->admin_parse_request();
		array_unshift( $this->services, 'admin' );
		add_action( 'admin_init', [ $this, 'init_service' ], 1 );
	}

	/**
	 * @access public
	 * @return (void)
	 */
	public function admin_parse_request() {
		global $pagenow;
		$q = filter_input_array( \INPUT_GET, self::$def, false ) ?: [];
		switch ( $pagenow ) {
			case 'edit.php' :
			case 'post-new.php' :
				$maybe_ns = array_key_exists( 'post_type', $q ) ? $q['post_type'] : null;
				break;
			case 'post.php' :
				/**
				 * @see https://ja.forums.wordpress.org/topic/150122
				 */
				$maybe_ns = array_key_exists( 'post', $q ) ? get_post_type( $q['post'] ) : filter_input( \INPUT_POST, 'post_type' );
				break;
			case 'edit-tags.php' :
				$maybe_ns = array_key_exists( 'taxonomy', $q ) ? $q['taxonomy'] : null;
				break;
			case 'index.php' :
				// _var_dump( 'Dashboard!!!!!' );
				break;
		}
		/**
		 * @uses WPDW\_domain()
		 * @link wp-domain-work/inc/functions.php
		 */
		if ( isset( $maybe_ns ) && ( $domain = _domain( $maybe_ns ) ) ) {
			$this->ns = $domain;
			$this->arguments = $q + [ 'domain' => $domain ];
		}
	}

	/**
	 * @access public
	 * @return (void)
	 */
	public function init_service() {
		if ( ! $this->ns )
			return;
		foreach ( $this->services as $service )
			$this->exec( $service );
	}

	/**
	 * @access private
	 * @param  string $cl
	 * @return (void)
	 */
	private function exec( $cl ) {
		$class = 'WP_Domain\\' . $this->ns . '\\' . $cl;
		if ( class_exists( $class ) )
			$class::init( $this->arguments );
	}

}
