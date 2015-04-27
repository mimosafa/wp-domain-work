<?php
namespace WPDW;

use mimosafa\ClassLoader as CL, WPDW\WP as WP;

/**
 * @uses WPDW\Util\Singleton
 * @uses WPDW\Options
 * @uses WPDW\Domains_Dir
 * @uses WPDW\WP\register_customs
 */
class Domains {
	use Util\Singleton { getInstance as init; }

	/**
	 * @var array
	 */
	private $domains;

	/**
	 * Constructor
	 *
	 * @access protected
	 */
	protected function __construct() {
		if ( $this->domains = Options::get_domains() ) {
			$this->init_domains();
		} else {
			if ( Options::get_domains_all() )
				return;
			$this->init_domains_dir();
			if ( $this->domains )
				$this->init_domains();
		}
	}

	/**
	 * @access private
	 */
	private function init_domains() {
		foreach ( $this->domains as $domain => $args ) {
			if ( array_key_exists( 'post_type', $args ) )
				$this->init_post_type( $args );
			else if ( array_key_exists( 'taxonomy', $args ) )
				$this->init_taxonomy( $args );
			# else if ( array_key_exists( 'endpoint', $args ) )
			# 	$this->init_endpoint( $args );
			$this->init_class_loader( $domain, $args['files'] );
		}
	}

	/**
	 * @access private
	 */
	private function init_domains_dir() {
		$dd = Domains_Dir::getInstance();
		if ( $this->domains = $dd->active_domains )
			Options::update_domains( $this->domains );
		if ( $domains_all = $dd->domains )
			Options::update_domains_all( $domains_all );
		if ( $domains_alias = $dd->domains_alias )
			Options::update_domains_alias( $domains_alias );
	}

	/**
	 * @access private
	 *
	 * @param  array $array
	 * @return (void)
	 */
	private function init_post_type( Array $args ) {
		WP\register_customs::custom_post_type( $args['post_type'], $args['label'], $args['options'] );
		if ( array_key_exists( 'capability_type', $args ) ) {
			//
		}
		if ( array_key_exists( 'rewrite', $args ) ) {
			//
		}
	}

	/**
	 * @access private
	 *
	 * @param  array $array
	 * @return (void)
	 */
	private function init_taxonomy( Array $args ) {
		WP\register_customs::custom_taxonomy( $args['taxonomy'], $args['label'], $args['post_types'], $args['options'] );
	}

	/**
	 * @access private
	 *
	 * @param  array $array
	 * @return (void)
	 */
	private function init_endpoint( Array $args ) {
		//
	}

	/**
	 * @access private
	 *
	 * @todo
	 *
	 * @param  string $domain
	 * @param  array  $files
	 */
	private function init_class_loader( $domain, Array $files ) {
		foreach ( $files as $file ) {
			$path = self::add_path_prefix( $file );
			if ( is_readable( $path ) ) {
				CL::register( 'WP_Domain\\' . $domain, dirname( $path ), CL::REMOVE_FIRST_NAMESPACE_STRING );
			} else {
				Options::update_domains( '' );
				Options::update_domains_all( '' );
				WP\admin_notices::error( 'Lost 1 or more Domain Directories.' );
				$this->__construct(); // Work ?
				break;
			}
		}
	}

	/**
	 * Add wp-content directory's path to path string
	 *
	 * @access public
	 *
	 * @see    WPDW\Domains_Dir::remove_path_prefix( $path )
	 *
	 * @param  string $path
	 * @return string
	 */
	public static function add_path_prefix( $path ) {
		return \WP_CONTENT_DIR . $path;
	}

}
