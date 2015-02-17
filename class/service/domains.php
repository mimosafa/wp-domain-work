<?php

namespace WP_Domain_Work\Service;

/**
 * alias
 */
use \WP_Domain_Work\Plugin as DW;

/**
 * 
 */
class Domains {

	/**
	 */
	const DOMAINS_DIR_NAME = 'domains';

	/**
	 */
	private $directories = [];

	/**
	 */
	private $domains_directories = [];

	/**
	 */
	private $domains = [];

	/**
	 */
	private $supports = [];

	/**
	 */
	private $functions_files = [];

	/**
	 * Default arguments for registering custom post type
	 * (Given priority than contents of the properties.php)
	 */
	private $_cpt_option = [
		'public'       => true,
		'has_archive'  => true,
		'hierarchical' => false,
		'rewrite'      => [ 'with_front' => false ],
		'supports'     => false,
	];

	/**
	 * Default arguments for registering custom taxonomy
	 * (Given priority than contents of the properties.php)
	 */
	private $_ct_option = [
		'public'    => true,
		'query_var' => true,
		'rewrite'   => [ 'with_front' => false ],
	];

	/**
	 * Except registring domains
	 * 
	 * @var array
	 */
	private static $_excepted = [
		/**
		 * WordPress core reserved words
		 *
		 * @see  http://codex.wordpress.org/Function_Reference/register_taxonomy#Reserved_Terms
		 */
		'attachment', 'attachment_id', 'author', 'author_name', 'calendar',
		'cat', 'category', 'category__and', 'category__in', 'category__not_in',
		'category_name', 'comments_per_page', 'comments_popup',
		'customize_messenger_channel', 'customized', 'cpage', 'day', 'debug',
		'error', 'exact', 'feed', 'hour', 'link_category', 'm', 'minute',
		'monthnum', 'more', 'name', 'nav_menu', 'nonce', 'nopaging', 'offset',
		'order', 'orderby', 'p', 'page', 'page_id', 'paged', 'pagename', 'pb',
		'perm', 'post', 'post__in', 'post__not_in', 'post_format', 'post_mime_type',
		'post_status', 'post_tag', 'post_type', 'posts', 'posts_per_archive_page',
		'posts_per_page', 'preview', 'robots', 's', 'search', 'second', 'sentence',
		'showposts', 'static', 'subpost', 'subpost_id', 'tag', 'tag__and', 'tag__in',
		'tag__not_in', 'tag_id', 'tag_slug__and', 'tag_slug__in', 'taxonomy', 'tb',
		'term', 'theme', 'type', 'w', 'withcomments', 'withoutcomments', 'year',
	];

	/**
	 */
	private static $property_data = [
		'name'              => 'Name',
		'plural_name'       => 'Plural Name',
		'register'          => 'Register As',
		'post_type'         => 'Post Type Name',
		'taxonomy'          => 'Taxonomy Name',
		'taxonomy_type'     => 'Taxonomy Type',
		'related_post_type' => 'Related Post Type',
		'rewrite'           => 'Permalink Format',
		'capability_type'   => 'Capability Type',
	];

	/**
	 */
	private static $admin_data = [
		'support'  => 'Support',
		'readonly' => 'Read Only',
	];

	//

	/**
	 */
	public function __construct( $force_scan = false ) {
		$this->init( $force_scan );
	}

	/**
	 */
	private function init( $force_scan ) {
		if ( ! $force_scan && $domains = DW::get_domains() ) {
			$this->domains = $domains;
			$this->domains_directories = DW::get_domains_dirs();
			$this->functions_files = DW::get_functions_files();
		} else {
			// wp-content/domains
			$this->directories[] = \WP_CONTENT_DIR . '/' . self::DOMAINS_DIR_NAME;
			// wp-content/themes/your-parent-theme/domains
			$this->directories[] = get_template_directory() . '/' . self::DOMAINS_DIR_NAME;
			// wp-content/themes/your-child-theme/domains
			$this->directories[] = get_stylesheet_directory() . '/' . self::DOMAINS_DIR_NAME;
			if ( $excepted_domains = DW::get_excepted_domains() ) {
				self::$_excepted = array_merge( $excepted_domains, self::$_excepted );
			}
			$this->scan_directories();
			/**
			 * update options
			 */
			if ( $this->domains ) {
				DW::update_domains( $this->domains );
				DW::update_domains_dirs( $this->domains_directories );
				DW::update_functions_files( $this->functions_files );
				DW::update_post_type_supports( $this->supports );
			}
			DW::flush_rewrite_rules();
		}

		if ( $this->domains ) {
			$this->classify_domains();
			$this->register_class_loaders();
		}
		if ( $this->functions_files ) {
			$this->include_functions_files();
		}
	}

	/**
	 */
	private function scan_directories() {
		$done = '';

		/**
		 * Outer loop (*1)
		 * 1. wp-content/domains
		 * 2. wp-content/themes/your-parent-theme/domains (parent theme)
		 * 3. wp-content/themes/your-child-theme/domains (child theme)
		 */
		foreach ( $this->directories as $path ) {
			/**
			 * Outer loop will be end in first loop, if TEMPLATEPATH equals STYLESHEETPATH
			 */
			if ( $path === $done ) {
				break;
			}
			if ( ! is_readable( $path ) ) {
				continue;
			}
			/**
			 * Files & dires in domains directory
			 */
			$dir = new \DirectoryIterator( $path );
			/**
			 * Inner loop
			 */
			foreach ( $dir as $fileinfo ) {
				if ( $fileinfo->isDot() || ! $fileinfo->isDir() ) {
					continue;
				}
				/**
				 * Directory's name will be used as domain name
				 */
				$domain = $fileinfo->getFilename();
				/**
				 * Refuse registering, if the domain set as excepted domain
				 */
				if ( in_array( $domain, self::$_excepted ) ) {
					continue;
				}
				/**
				 * Retrieve metadata from properties.php
				 * if file is not exist, continue
				 *
				 * @see http://dogmap.jp/2014/09/10/post-3109/
				 */
				if ( ! $property_file = self::returnReadableFilePath( $fileinfo, 'properties.php' ) ) {
					continue;
				}
				if ( ! $property = array_filter( get_file_data( $property_file, self::$property_data ) ) ) {
					continue;
				}

				/**
				 * 親テーマの場合はそのまま代入、子テーマの場合 (すでに $domainsに要素がある場合)はマージする。
				 */
				if ( ! array_key_exists( $domain, $this->domains ) ) {
					$this->domains[$domain] = $property;
				} else {
					$this->domains[$domain] = array_merge( $this->domains[$domain], $property );
				}
				/**
				 * Custom Post Types' support data
				 */
				if ( $admin_file = self::returnReadableFilePath( $fileinfo, 'admin.php' ) ) {
					$supports = array_filter( get_file_data( $admin_file, self::$admin_data ) );
					if ( $supports ) {
						foreach ( $supports as $key => $string ) {
							$array = array_map( function( $str ) {
								return trim( $str );
							}, explode( ',', $string ) );
							foreach ( $array as $var ) {
								$this->supports[$domain][$var] = $key;
							}
						}
					}
				}

				if ( $functions_path = self::returnReadableFilePath( $fileinfo, 'functions.php' ) ) {
					array_unshift( $this->functions_files, self::remove_path_prefix( $functions_path ) );
				}
			}
			array_unshift( $this->domains_directories, self::remove_path_prefix( $path ) );
			$done = $path;
		}
	}

	/**
	 */
	private function classify_domains() {
		foreach ( $this->domains as $domain => $array ) {
			if ( 'Custom Post Type' === $array['register'] ) {
				$this->post_type_setting( $domain, $array );
			} elseif ( 'Custom Taxonomy' === $array['register'] ) {
				$this->taxonomy_setting( $domain, $array );
			} elseif ( 'Custom Endpoint' === $array['register'] ) {
				$this->endpoint_setting( $domain, $array );
			}
		}
	}

	/**
	 */
	private function post_type_setting( $domain, Array $array ) {
		/**
		 * Name (slug)
		 */
		if ( array_key_exists( 'post_type', $array ) ) {
			$post_type = strtolower( $array['post_type'] );
			if ( in_array( $post_type, self::$_excepted ) ) {
				return;
			}
		} else {
			$post_type = $domain;
		}

		/**
		 * Label
		 */
		$label = ( array_key_exists( 'name', $array ) )
			? esc_html( $array['name'] )
			: ucwords( str_replace( '_', ' ', $post_type ) )
		;

		/**
		 * Options (rewrite, capability_type, )
		 */
		
		/**
		 * Rewrite slug
		 */
		$opt = [ 'rewrite' => [ 'slug' => $domain ] ];

		/**
		 * Capability type
		 */
		/*
		if ( array_key_exists( 'capability_type', $array ) ) {
			$cap_type = array_map( function( $var ) {
				return trim( $var );
			}, explode( ',', $array['capability_type'] ) );
			if ( 2 === count( $cap_type ) ) {
				$opt['capability_type'] = $cap_type;
				$opt['map_meta_cap'] = true

				if ( null === self::$roles ) {
					self::$roles = new \wordpress\roles();
				}
				self::$roles->add_cap( $cap_type );
			}
		}
		*/

		/**
		 * Merge default setting to each post type setting
		 */
		$opt = \utility\md_array_merge( $opt, $this->_cpt_option );

		\WP_Domain_Work\WP\register_customs::add_post_type( $post_type, $label, $opt );

		if ( array_key_exists( 'rewrite', $array ) && 'ID' === $array['rewrite'] ) {
			\WP_Domain_Work\WP\int_permalink::set( $post_type );
		}
	}

	/**
	 */
	private function taxonomy_setting( $domain, Array $array ) {
		/**
		 * Name (slug)
		 */
		if ( array_key_exists( 'taxonomy', $array ) ) {
			$taxonomy = strtolower( $array['taxonomy'] );
			if ( in_array( $taxonomy, self::$_excepted ) ) {
				return;
			}
		} else {
			$taxonomy = $domain;
		}
		/**
		 * Label
		 */
		$label = array_key_exists( 'name', $array )
			? esc_html( $array['name'] )
			: ucwords( str_replace( '_', ' ', $taxonomy ) )
		;
		/**
		 * Post types
		 */
		$post_types = explode( ',', $array['related_post_type'] );
		$post_types = array_map( function( $string ) {
			return trim( $string );
		}, $post_types );
		/**
		 * Rewrite
		 */
		$opt = [ 'rewrite' => [ 'slug' => $domain ] ];
		if ( array_key_exists( 'taxonomy_type', $array ) && 'Category' === $array['taxonomy_type'] ) {
			$opt['hierarchical'] = true;
			$opt['rewrite']['hierarchical'] = true;
		}
		// ~
		$opt = \utility\md_array_merge( $opt, $this->_ct_option );

		\WP_Domain_Work\WP\register_customs::add_taxonomy( $taxonomy, $label, $post_types, $opt );
	}

	/**
	 */
	private function endpoint_setting( $domain, Array $array ) {
		// ~ some settings from $array, but yet...
		\WP_Domain_Work\WP\create_endpoints::set( $domain );
	}

	/**
	 */
	private function register_class_loaders() {
		foreach ( $this->domains_directories as $dir ) {
			$path = self::add_path_prefix( $dir );
			if ( is_readable( $path ) ) {
				\ClassLoader::register( 'WP_Domain', $path, \ClassLoader::REMOVE_FIRST_NAMESPACE_STRING );
			} else {
				new self( true );
				//
				break;
			}
		}
	}

	/**
	 */
	private function include_functions_files() {
		foreach ( $this->functions_files as $file ) {
			$file = self::add_path_prefix( $file );
			if ( file_exists( $file ) ) {
				require_once $file;
			} else {
				new self( true );
					add_action( 'admin_init', function() {
						add_settings_error(
							'wp-domain-work',
							'function-file-removed',
							'1 or more domain\'s functions.php are deleted. You shoud do "Force Directories Search".',
							'error'
						);
					} );
				break;
			}
		}
	}

	/**
	 * @access private
	 *
	 * @param  DirectoryIterator $fileinfo (required)
	 * @param  string $file (required)
	 * @return mixed bool|string Return file path string, if $file exists.
	 */
	private static function returnReadableFilePath( $fileinfo, $file ) {
		if ( 'DirectoryIterator' === get_class( $fileinfo ) ) {
			$path = $fileinfo->getPathname() . '/' . ltrim( $file, '/' );
			if ( is_readable( $path ) ) {
				return $path;
			}
		}
		return false;
	}

	/**
	 * add wp-content directory's path to path string
	 *
	 * @access public
	 *
	 * @param  string $path
	 * @return string
	 */
	public static function add_path_prefix( $path ) {
		return \WP_CONTENT_DIR . $path;
	}

	/**
	 * remove wp-content directory's path from path string
	 *
	 * @access public
	 *
	 * @param  string $path
	 * @return string
	 */
	public static function remove_path_prefix( $path ) {
		static $start = null;
		if ( null === $start ) {
			$start = strlen( \WP_CONTENT_DIR );
		}
		return substr( $path, $start );
	}

}
