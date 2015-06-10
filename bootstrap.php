<?php
/**
 * Class Loader
 *
 * @uses mimosafa\ClassLoader
 */
use mimosafa\ClassLoader as CL;

require_once __DIR__ . '/lib/mimosafa/ClassLoader.php';
// Plugin classes
CL::register( 'WPDW', __DIR__ . '/class',
	CL::FILENAME_STRTOLOWER | CL::FILENAME_UNDERBAR_AS_HYPHEN | CL::NAMESPACE_STRTOLOWER |
	CL::NAMESPACE_UNDERBAR_AS_HYPHEN | CL::REMOVE_FIRST_NAMESPACE_STRING
);
// Other libraries
CL::register( 'mimosafa', __DIR__ . '/lib' );

/**
 * Initialize WP Domain Work plugin function
 *
 * @return (void)
 */
function initialize_wp_domain_work_plugin() {
	WP_Domain_Work::getInstance();
}

/**
 * Plugin bootstrap
 *
 * @uses WPDW\Util\Singleton
 * @uses WPDW\Options
 * @uses WPDW\Domains
 * @uses WPDW\Router
 * @uses WPDW\WP\admin_notices
 */
use WPDW\Options as OPT, WPDW\WP as WP;

class WP_Domain_Work {
	use WPDW\Util\Singleton;
	
	/**
	 * Constructor
	 *
	 * @access protected
	 */
	protected function __construct() {
		set_exception_handler( [ &$this, 'exception_handler' ] );
		add_filter( 'pre_update_option', [ &$this, 'pre_update_option' ], 10, 3 );
		register_activation_hook( WPDW_PLUGIN_FILE, [ &$this, 'activation' ] );
		register_deactivation_hook( WPDW_PLUGIN_FILE, [ &$this, 'deactivation' ] );
		$this->init();
	}

	/**
	 * @access private
	 */
	private function init() {
		if ( $this->is_using_domains_dir() ) {
			require_once WPDW_PLUGIN_DIR . '/inc/functions.php';
			add_action( 'setup_theme', 'WPDW\Domains::init' );
			add_action( 'setup_theme', 'WPDW\Router::init' );
		}
		if ( is_admin() )
			add_action( 'init', 'WPDW\Settings::init' );
		WPDW\Scripts::init();
	}

	/**
	 * Plugin activating action
	 * 
	 * @return (void)
	 */
	public function activation() {
		//
	}

	/**
	 * Plugin deactivating action
	 */
	public function deactivation() {
		//
	}

	/**
	 * Confirm using domains dir or not
	 *
	 * @access private
	 * @uses   WPDW\Options
	 * @return int|boolean
	 */
	private function is_using_domains_dir() {
		return OPT::get_root_domains() || OPT::get_theme_domains() || OPT::get_sample_domains();
	}

	/**
	 * @access private
	 */
	public function pre_update_option( $value, $option, $old_value ) {
		if ( ! doing_action( 'pre_update_option' ) )
			return;
		if ( $value === $old_value )
			return $value;
		switch ( $option ) {
			case OPT::getKey( 'sample_domains' ) :
			case OPT::getKey( 'root_domains' ) :
			case OPT::getKey( 'theme_domains' ) :
				OPT::update_domains( [] );
				OPT::update_domains_all( [] );
				break;
			case OPT::getKey( 'force_dir_scan' ) :
				OPT::update_domains( [] );
				OPT::update_domains_all( [] );
				$value = $old_value;
				break;
		}
		return $value;
	}

	/**
	 * For Administrator: Exception handler
	 *
	 * @param  Exception $e
	 */
	public function exception_handler( Exception $e ) {
		if ( is_super_admin() ) {
			echo esc_html( $e->getMessage() );
			echo '<pre>';
			var_dump( $e->getTrace() );
			echo '</pre>';
		}
	}

}
