<?php
namespace WPDW;

/**
 * @uses WPDW\Util\Singleton
 * @uses WPDW\Util\String_Function
 * @uses WPDW\Options
 */
class Domains_Dir {
	use Util\Singleton, Util\String_Function, Util\Array_Function;

	/**
	 * @var array
	 */
	private $dirs;

	/**
	 * @var array
	 */
	private $domains = [];
	private $active_domains = [];
	private $domains_alias  = [];

	/**
	 * Exclude domains
	 *
	 * @var array
	 */
	private $excluded;

	/**
	 * @var boolean
	 */
	private $error  = false;
	private $notice = false;

	/**
	 * @var string
	 */
	private static $_sample_domains_dir;
	private static $_root_domains_dir;
	private static $_template_domains_dir;
	private static $_stylesheet_domains_dir;

	/**
	 * @var array
	 */
	private static $_property_data = [
		'label'             => 'Label',
		'plural'            => 'Plural Name',
		'register'          => 'Register As',
		'post_type'         => 'Post Type Name',
		'taxonomy'          => 'Taxonomy Name',
		'taxonomy_type'     => 'Taxonomy Type',
		'related_post_type' => 'Related Post Type',
		'rewrite'           => 'Permalink Format',
		'capability_type'   => 'Capability Type',
		'supports'          => 'Supports',
		'menu_icon'         => 'Menu Icon',
	];

	/**
	 * @var array
	 */
	private static $_registerable = [ 'Custom Post Type', 'Custom Taxonomy', 'Custom Endpoint' ];

	/**
	 * @var array
	 */
	private static $_post_type_options = [
		'public'       => true,
		'has_archive'  => true,
		'hierarchical' => false,
		//'supports'     => false,
	];
	private static $_taxonomy_options = [
		'public'    => true,
		'query_var' => true,
	];

	/**
	 * @var string
	 */
	private static $_error_message_1 = 'You\'ve enabled the %s domains directory, but %s not exist.';
	private static $_error_message_2 = 'You\'ve enabled the %s domains directory, but there\'re no valid domains in %s.';

	/**
	 * Getter
	 *
	 * @access public
	 */
	public function __get( $name ) {
		if ( property_exists( __CLASS__, $name ) )
			return $this->$name;
		return null;
	}

	/**
	 * Constructor
	 */
	protected function __construct() {
		$this->prepare();
		if ( ! $this->dirs = $this->return_existing_dirs() )
			return;
		$this->excluded = Options::get_excluded_domains() ?: [];
		$this->init();
		$this->shutdown();
	}

	/**
	 * @access private
	 *
	 * @return (void)
	 */
	private function prepare() {
		self::$_sample_domains_dir = \WPDW_PLUGIN_DIR . '/domains';
		self::$_root_domains_dir = \WP_CONTENT_DIR . '/domains';
		self::$_template_domains_dir = get_template_directory() . '/domains';
		self::$_stylesheet_domains_dir = get_stylesheet_directory() . '/domains';
	}

	/**
	 * Prepare domains directories
	 * 
	 * @return array
	 */
	private function return_existing_dirs() {
		$dirs = [];
		if ( $sample_dir = $this->sample_domains_dir() )
			$dirs['sample'] = $sample_dir;
		if ( $root_dir = $this->root_domains_dir() )
			$dirs['root'] = $root_dir;
		if ( $theme_dirs = $this->theme_domains_dir() )
			$dirs = array_merge( $dirs, $theme_dirs );
		return $dirs;
	}

	/**
	 * @access public
	 *
	 * @return string|boolean
	 */
	public static function sample_domains_dir() {
		if ( ! Options::get_sample_domains() )
			return false;
		$path = self::$_sample_domains_dir;
		if ( is_readable( $path ) )
			return $path;
		Options::update_sample_domains( '' );
		WP\admin_notices::error( sprintf( __( self::$_error_message_1 ), 'sample', '<code>' . $path . '</code>' ) );
		return false;
	}

	/**
	 * @access public
	 *
	 * @return string|boolean
	 */
	public static function root_domains_dir() {
		if ( ! Options::get_root_domains() )
			return false;
		$path = self::$_root_domains_dir;
		if ( is_readable( $path ) )
			return $path;
		Options::update_root_domains( '' );
		WP\admin_notices::error( sprintf( __( self::$_error_message_1 ), 'root', '<code>' . $path . '</code>' ) );
		return false;
	}

	/**
	 * @access public
	 *
	 * @return array|boolean
	 */
	public static function theme_domains_dir() {
		if ( ! Options::get_theme_domains() )
			return false;
		$dirs = array_filter(
			array_unique( [ self::$_template_domains_dir, self::$_stylesheet_domains_dir ] ),
			function( $path ) { return is_readable( $path ); }
		);
		if ( $dirs )
			return $dirs;
		Options::update_theme_domains( '' );
		$string = '<code>' . self::$_template_domains_dir . '</code>';
		if ( self::$_template_domains_dir !== self::$_stylesheet_domains_dir )
			$string .= ', <code>' . self::$_stylesheet_domains_dir . '</code>';
		WP\admin_notices::error( sprintf(__( self::$_error_message_1 ), 'theme', $string ) );
		return false;
	}

	/**
	 * @access private
	 *
	 * @uses   DirectoryIterator
	 * @uses   WPDW\Util\String_Function::toArray()
	 * @uses   WPDW\Util\Array_Function::md_merge()
	 * @global $wp
	 */
	private function init() {
		foreach ( $this->dirs as $key => $path ) {
			$dir = new \DirectoryIterator( $path );

			// Counter of valid directories
			$has = 0;

			global $wp;
			$reserved  = $wp->public_query_vars + $wp->private_query_vars;

			foreach ( $dir as $fileinfo ) {
				if ( $fileinfo->isDot() || ! $fileinfo->isDir() )
					continue;

				$domain = $fileinfo->getFilename(); // Directory name is used as the domain name
				$file   = self::remove_path_prefix( $fileinfo->getPathname() );
				$overwrite = array_key_exists( $domain, $this->domains );
				$property  = [];
				$error = '';
				$notice = '';

				if ( preg_match( '/^[^a-z]|[^a-z0-9_\-]/', $domain ) || strlen( $domain ) > 20 ) {
					$error = 'Invalid Domain Name';
				} else if ( in_array( $domain, $reserved, true ) ) :
					$error = 'Domain Name is Reserved Word';
				elseif ( ! $property_file = $this->return_readable_file_path( $fileinfo, 'property.php' ) ) :
					$error = 'File Not Exist';
				/**
				 * Retrieve metadata from properties.php
				 *
				 * @see http://dogmap.jp/2014/09/10/post-3109/
				 */
				elseif ( ! $property = array_filter( get_file_data( $property_file, self::$_property_data ) ) ) :
					$error = 'No Data';
				else :
					if ( array_key_exists( 'register', $property ) ) {
						if ( ! in_array( $property['register'], self::$_registerable, true ) ) {
							$error = 'Invalid Registration';
							unset( $property['register'] );
						} else if ( $overwrite && array_key_exists( 'register', $this->domains[$domain] ) ) {
							if ( $property['register'] !== $this->domains[$domain]['register'] ) {
								$error = 'Overwrite Protection';
								unset( $property['register'] );
							}
						} else {
							if ( $property['register'] === 'Custom Post Type' && array_key_exists( 'post_type', $property ) ) {
								if ( in_array( $property['post_type'], $reserved, true ) ) {
									$notice[] = 'Post Type Name is Reserved Word';
									unset( $property['post_type'] );
								}
							} else if ( $property['register'] === 'Custom Taxonomy' && array_key_exists( 'taxonomy', $property ) ) {
								if ( in_array( $property['taxonomy'], $reserved, true ) ) {
									$notice[] = 'Taxonomy Name is Reserved Word';
									unset( $property['taxonomy'] );
								}
							}
						}
					}
					array_walk( $property, function( &$arg, $key ) {
						if ( in_array( $key, [ 'related_post_type', 'capability_type', 'supports' ], true ) )
							$arg = self::toArray( $arg );
					} );
				endif;

				if ( ! $error )
					$has++;
				else
					$property['errors'] = [ $file => $error ];
				if ( $notice )
					$property['notices'] = [ $file => $notice ];

				if ( ! $overwrite ) {
					if ( ! $error )
						$property['files'] = [ $file ];
					$this->domains[$domain] = $property;
				} else {
					$this->domains[$domain] = self::md_merge( $this->domains[$domain], $property );
					if ( ! $error )
						$this->domains[$domain]['files'][] = $file;
				}
			}
			if ( ! $has )
				unset( $this->dirs[$key] );
		}
		if ( $this->domains )
			array_walk( $this->domains, [ &$this, 'verify_domains'] );
	}

	/**
	 * @access private
	 *
	 * @param  Traversable $fileinfo
	 * @param  string $file
	 * @return mixed bool|string Return file path string, if $file exists.
	 */
	private function return_readable_file_path( \Traversable $fileinfo, $file ) {
		$path = $fileinfo->getPathname() . '/' . ltrim( $file, '/' );
		if ( is_readable( $path ) )
			return $path;
		return false;
	}

	/**
	 * remove wp-content directory's path from path string
	 *
	 * @access public
	 *
	 * @see    WPDW\Domains::add_path_prefix( $path )
	 *
	 * @param  string $path
	 * @return string
	 */
	public static function remove_path_prefix( $path ) {
		static $start = null;
		if ( null === $start )
			$start = strlen( \WP_CONTENT_DIR );
		return substr( $path, $start );
	}

	/**
	 * @access private
	 */
	private function verify_domains( Array &$property, $domain ) {
		if ( array_key_exists( 'register', $property ) ) {
			if ( in_array( $domain, $this->excluded, true ) ) {
				$status = 'excluded';
			} else {
				$status = 'success';
				$this->active_domains[$domain] = $this->prepared_property( $domain, $property );
				$this->active_domains[$domain]['files'] = array_reverse( $property['files'] );
			}
		} else {
			$status = 'fail';
			if ( ! array_key_exists( 'errors', $property ) ) {
				$property['errors'] = array_map(
					function() {
						return 'No Registration';
					}, array_flip( $property['files'] )
				);
				unset( $property['files'] );
			} else if ( array_key_exists( 'files', $property ) ) {
				$errs = array_combine( $property['files'], array_fill( 0, count( $property['files'] ), 'No Registration' ) );
				$property['errors'] = array_merge( $property['errors'], $errs );
				unset( $property['files'] );
			}
		}
		$property = array_merge( $property, [ 'status' => $status ] );

		// ERROR
		if ( ! $this->error && array_key_exists( 'errors', $property ) )
			$this->error = true;
		// NOTICE
		if ( ! $this->notice && array_key_exists( 'notices', $property ) )
			$this->notice = true;
	}

	/**
	 * @access private
	 *
	 * @param  string $domain
	 * @param  array  $args
	 */
	private function prepared_property( $domain, Array $args ) {
		switch ( $args['register'] ) {
			case 'Custom Post Type' :
				return $this->post_type_args( $domain, $args );
			case 'Custom Taxonomy' :
				return $this->taxonomy_args( $domain, $args );
			case 'Custom Endpoint' :
				return $this->endpoint_args( $domain, $args );
		}
	}

	/**
	 * @access private
	 *
	 * @param  string $domain
	 * @param  array  $args
	 */
	private function post_type_args( $domain, Array $args ) {
		$return = [];
		// post_type
		$return['post_type'] = $this->domain_alias( 'post_type', $args ) ?: $domain;
		// Domains name on query argument
		$this->domains_alias[$return['post_type']] = $domain;
		// label
		$return['label'] = array_key_exists( 'label', $args )
			? $args['label']
			: ucwords( str_replace( '_', ' ', $domain ) )
		;
		// options
		$return['options'] = [];
		$opt =& $return['options'];
		// rewrite
		if ( array_key_exists( 'rewrite', $args ) && 'ID' === $args['rewrite'] ) {
			$return['rewrite'] = 'ID';
		} else {
			$opt['rewrite'] = [ 'with_front' => false, 'slug' => $domain ];
		}
		if ( array_key_exists( 'capability_type', $args ) ) {
			$cap_type = $args['capability_type'];
			if ( 2 === count( $cap_type ) && $cap_type[0] !== $cap_type[1] ) {
				$opt['capability_type'] = $cap_type;
				$opt['map_meta_cap'] = true;
			}
		}
		if ( array_key_exists( 'supports', $args ) ) {
			static $post_thumbnails_support = false;
			if ( ! $post_thumbnails_support && in_array( 'thumbnail', $args['supports'], true ) ) {
				add_theme_support( 'post-thumbnails' );
				$post_thumbnails_support = true;
			}
			$opt['supports'] = $args['supports'];
		}
		if ( array_key_exists( 'menu_icon', $args ) ) {
			$opt['menu_icon'] = $args['menu_icon'];
		}
		$opt = self::md_merge( self::$_post_type_options, $opt );
		return $return;
	}

	/**
	 * @access private
	 *
	 * @param  string $domain
	 * @param  array  $args
	 */
	private function taxonomy_args( $domain, Array $args ) {
		$return = [];
		// taxonomy
		$return['taxonomy'] = $this->domain_alias( 'taxonomy', $args ) ?: $domain;
		// Domains name on query argument
		$this->domains_alias[$return['taxonomy']] = $domain;
		// label
		$return['label'] = array_key_exists( 'label', $args )
			? $args['label']
			: ucwords( str_replace( '_', ' ', $domain ) )
		;
		// post_types
		$return['post_types'] = array_key_exists( 'related_post_type', $args ) ? $args['related_post_type'] : [];
		// options
		$return['options'] = [];
		$opt =& $return['options'];
		$opt = [ 'rewrite' => [ 'with_front' => false, 'slug' => $domain ] ];
		if ( array_key_exists( 'taxonomy_type', $args ) && 'Category' === $args['taxonomy_type'] ) {
			$opt['hierarchical'] = true;
			$opt['rewrite']['hierarchical'] = true;
		}
		$opt = self::md_merge( self::$_taxonomy_options, $opt );
		return $return;
	}

	/**
	 * @access private
	 *
	 * @param  string $domain
	 * @param  array  $args
	 */
	private function endpoint_args( $domain, Array $args ) {
		$return = [];
		$return['endpoint'] = $domain;
		$return['label'] = array_key_exists( 'label', $args )
			? $args['label']
			: ucwords( str_replace( '_', ' ', $domain ) )
		;
		// ~
		return $return;
	}

	/**
	 * @access private
	 *
	 * @param  string $whitch post_type|taxonomy
	 * @return string|boolean
	 */
	private function domain_alias( $whitch, Array $args ) {
		return array_key_exists( $whitch, $args ) && ! preg_match( '/[^a-z0-9_\-]/', $args[$whitch] )
			? $args[$whitch] : false;
		;
	}

	/**
	 * @access private
	 */
	private function shutdown() {
		if ( Options::get_sample_domains() && ! array_key_exists( 'sample', $this->dirs ) ) {
			Options::update_sample_domains( '' );
			WP\admin_notices::error( sprintf( __( self::$_error_message_2 ), 'sample', '<code>' . self::$_sample_domains_dir . '</code>' ) );
		}
		if ( Options::get_root_domains() && ! array_key_exists( 'root', $this->dirs ) ) {
			Options::update_root_domains( '' );
			WP\admin_notices::error( sprintf( __( self::$_error_message_2 ), 'root', '<code>' . self::$_root_domains_dir . '</code>' ) );
		}
		if ( Options::get_theme_domains() && ! array_key_exists( 0, $this->dirs ) && ! array_key_exists( 1, $this->dirs ) ) {
			Options::update_theme_domains( '' );
			$string = '<code>' . self::$_template_domains_dir . '</code>';
			if ( self::$_template_domains_dir !== self::$_stylesheet_domains_dir )
				$string .= ' and <code>' . self::$_stylesheet_domains_dir . '</code>';
			WP\admin_notices::error( sprintf( __( self::$_error_message_2 ), 'root', $string ) );
		}
	}

}
