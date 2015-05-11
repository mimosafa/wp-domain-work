<?php
namespace WPDW;

/**
 * @uses   WPDW\_domain()
 * @uses   WPDW\_alias()
 * @see    wp-domain-work/inc/functions.php
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
	private $services = [ 'query' ];

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
	 * @return (void)
	 */
	protected function __construct() {
		! is_admin() ?  $this->template_redirect() : $this->admin_init();
	}

	/**
	 * Frontend hook
	 * @access private
	 * @return (void)
	 */
	private function template_redirect() {
		add_action( 'template_redirect', [ $this, 'parse_request' ], 0 );
		add_action( 'template_redirect', [ $this, 'init_service' ], 0 );
	}

	/**
	 * @access public
	 *
	 * @uses   WPDW\_domain()
	 * @uses   WPDW\_alias()
	 * @see    wp-domain-work/inc/functions.php
	 *
	 * @return (void)
	 */
	public function parse_request() {
		global $wp;
		if ( $wp->did_permalink ) {
			$path = explode( '/', $wp->request );
			$topPath = array_shift( $path );
			if ( $topPath && _alias( $topPath ) )
				$this->ns = $topPath;
		} else if ( $q = $wp->query_vars ) {
			if ( isset( $q['post_type'] ) && ( $domain = _domain( $q['post_type'] ) ) ) {
				$this->ns = $domain;
			} else {
				# $excluded = $wp->public_query_vars + $wp->private_query_vars;
				foreach ( $q as $key => $val ) {
					if ( $domian = _domain( $key ) ) {
						$this->ns = $domain;
						break;
					}
				}
			}
		} else {
			// Home
		}
	}

	/**
	 * Admin hook
	 * @access private
	 * @return (void)
	 */
	private function admin_init() {
		$this->admin_parse_request();
		array_push( $this->services, 'admin' );
		add_action( 'admin_init', [ $this, 'init_service' ], 0 );
	}

	/**
	 * @access public
	 *
	 * @uses   WPDW\_domain()
	 * @see    wp-domain-work/inc/functions.php
	 *
	 * @return (void)
	 */
	public function admin_parse_request() {
		global $pagenow;
		$q = filter_input_array( \INPUT_GET, self::$def );
		switch ( $pagenow ) {
			case 'edit.php' :
			case 'post-new.php' :
				$maybe_ns = $q['post_type'] ?: null;
				break;
			case 'post.php' :
				/**
				 * @see https://ja.forums.wordpress.org/topic/150122
				 */
				$maybe_ns = $q['post'] ? get_post_type( $q['post'] ) : filter_input( \INPUT_POST, 'post_type' );
				break;
			case 'edit-tags.php' :
				$maybe_ns = $q['taxonomy'] ?: null;
				break;
			case 'index.php' :
				// _var_dump( 'Dashboard!!!!!' );
				break;
		}
		if ( isset( $maybe_ns ) && ( $domain = _domain( $maybe_ns ) ) )
			$this->ns = $domain;
	}

	/**
	 * @access public
	 * 
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
	 * 
	 * @param  string $cl
	 * @return (void)
	 */
	private function exec( $cl ) {
		$class = 'WP_Domain\\' . $this->ns . '\\' . $cl;
		if ( class_exists( $class ) )
			$class::getInstance();
	}

}
