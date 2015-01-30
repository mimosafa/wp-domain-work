<?php

namespace service;

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
	private $domains = [];

	/**
	 */
	private $class_loaders = [];

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
		'name'            => 'Name',
		'plural_name'     => 'Plural Name',
		'register'        => 'Register As',
		'post_type'       => 'Post Type Name',
		'taxonomy'        => 'Taxonomy Name',
		'taxonomy_type'   => 'Taxonomy Type',
		'related_post_type' => 'Related Post Type',
		'rewrite'         => 'Permalink Format',
		'capability_type' => 'Capability Type',
	];

	//

	/**
	 */
	public function __construct( $force_scan = false ) {
		$this -> init( $force_scan );
	}

	/**
	 */
	private function init( $force_scan ) {
		/**
		 * Get instance plugin class
		 */
		$_WPDW = \WP_Domain_Work::getInstance();

		if ( !$force_scan && $domains = $_WPDW::get_domains() ) {
			$this -> domains = $domains;
			$this -> class_loaders = $_WPDW::get_class_loaders();
			$this -> functions_files = $_WPDW::get_functions_files();
		} else {
			// wp-content/domains
			$this -> directories[] = \WP_CONTENT_DIR . '/' . self::DOMAINS_DIR_NAME;
			// wp-content/themes/your-parent-theme/domains
			$this -> directories[] = get_template_directory() . '/' . self::DOMAINS_DIR_NAME;
			// wp-content/themes/your-child-theme/domains
			$this -> directories[] = get_stylesheet_directory() . '/' . self::DOMAINS_DIR_NAME;
			if ( $excepted_domains = $_WPDW::get_excepted_domains() ) {
				self::$_excepted = array_merge( $excepted_domains, self::$_excepted );
			}
			$this -> scan_directories();

			/**
			 * update options
			 */
			if ( $this -> domains ) {
				$_WPDW::update_domains( $this -> domains );
				$_WPDW::update_class_loaders( $this -> class_loaders );
				$_WPDW::update_functions_files( $this -> functions_files );
			}
			$_WPDW::flush_rewrite_rules();
		}

		if ( $this -> domains ) {
			$this -> classify_domains();
		}
		if ( $this -> class_loaders ) {
			$this -> register_class_loaders();
		}
		if ( $this -> functions_files ) {
			$this -> include_functions_files();
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
		foreach ( $this -> directories as $path ) {
			/**
			 * Outer loop will be end in first loop, if TEMPLATEPATH equals STYLESHEETPATH
			 */
			if ( $path === $done ) {
				break;
			}

			if ( !is_readable( $path ) ) {
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
				if ( $fileinfo -> isDot() || !$fileinfo -> isDir() ) {
					continue;
				}

				/**
				 * Directory's name will be used as domain name
				 */
				$domain = $fileinfo -> getFilename();

				/**
				 * Refuse registering, if the domain set as excepted domain
				 */
				if ( in_array( $domain, self::$_excepted ) ) {
					continue;
				}

				/**
				 * (*2) クラスのオートローダーとヘルパー関数が定義されたファイル (functions.php)
				 * - ディレクトリーループ (*1)で 2巡目以降のみを対象。
				 * - あとに読み込んだローダー、ファイルを優先したいので、array_unshiftで配列の先頭に追加する。
				 */
				if ( array_key_exists( $domain, $this -> class_loaders ) ) {
					array_unshift( $this -> class_loaders[$domain], self::remove_path_prefix( $path ) );
					if ( $functions_path = self::returnReadableFilePath( $fileinfo, 'functions.php' ) ) {
						array_unshift( $this -> functions_files, self::remove_path_prefix( $functions_path ) );
					}
				}

				/**
				 * Retrieve metadata from properties.php
				 * if file is not exist, continue
				 *
				 * @see http://dogmap.jp/2014/09/10/post-3109/
				 */
				if ( !$property_file = self::returnReadableFilePath( $fileinfo, 'properties.php' ) ) {
					continue;
				}
				$property = array_filter( get_file_data( $property_file, self::$property_data ) );

				// 親テーマの場合はそのまま代入、子テーマの場合 (すでに $domainsに要素がある場合)はマージする。
				if ( !array_key_exists( $domain, $this -> domains ) ) {
					$this -> domains[$domain] = $property;
				} else {
					$this -> domains[$domain] = array_merge( $this -> domains[$domain], $property );
				}

				/**
				 * クラスのオートローダーとヘルパー関数が定義されたファイル (functions.php)
				 * - (*2) 以外が対象
				 */
				if ( !array_key_exists( $domain, $this -> class_loaders ) ) {
					$this -> class_loaders[$domain][] = self::remove_path_prefix( $path );
					if ( $functions_path = self::returnReadableFilePath( $fileinfo, 'functions.php' ) ) {
						array_unshift( $this -> functions_files, self::remove_path_prefix( $functions_path ) );
					}
				}
			}
			$done = $path;
		}
	}

	/**
	 */
	private function classify_domains() {
		foreach ( $this -> domains as $domain => $array ) {
			if ( 'Custom Post Type' === $array['register'] ) {
				$this -> post_type_setting( $domain, $array );
			} elseif ( 'Custom Taxonomy' === $array['register'] ) {
				$this -> taxonomy_setting( $domain, $array );
			} elseif ( 'Custom Endpoint' === $array['register'] ) {
				$this -> endpoint_setting( $domain, $array );
			} else {
				unset( $this -> domains[$domain] );
			}
		}
	}

	/**
	 */
	private function post_type_setting( $domain, Array $array ) {
		/**
		 * Name (slug)
		 */
		$post_type = ( array_key_exists( 'post_type', $array ) )
			? esc_html( $array['post_type'] )
			: esc_html( $domain )
		;

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
				self::$roles -> add_cap( $cap_type );
			}
		}
		*/

		/**
		 * Merge default setting to each post type setting
		 */
		$opt = \utility\md_array_merge( $opt, $this -> _cpt_option );

		/**
		 * Get instance \wordpress\register_customs
		 */
		$registerCustoms = \wordpress\register_customs::getInstance();
		$registerCustoms -> add_post_type( $post_type, $label, $opt );

		/**
		 * 
		 */
		if ( array_key_exists( 'rewrite', $array ) && 'ID' === $array['rewrite'] ) {

			/**
			 * Get instance \wordpress\int_permalink
			 */
			$intPermalink = \wordpress\int_permalink::getInstance();
			$intPermalink -> set( $post_type );

		}
	}

	/**
	 */
	private function taxonomy_setting( $domain, Array $array ) {
		//
	}

	/**
	 */
	private function endpoint_setting( $domain, Array $array ) {
		// ~ some settings from $array, but yet...
		$createEndpoints = \wordpress\create_endpoints::getInstance();
		$createEndpoints -> set( $domain );
	}

	/**
	 */
	private function register_class_loaders() {
		foreach ( $this -> class_loaders as $domain => $somePath ) {
			foreach ( $somePath as $path ) {
				$path = self::add_path_prefix( $path );
				if ( is_readable( $path ) ) {
					\ClassLoader::register( $domain, $path, \ClassLoader::UNDERBAR_AS_HYPHEN );
				} else {
					new self( true );
					break 2;
				}
			}
		}
	}

	/**
	 */
	private function include_functions_files() {
		foreach ( $this -> functions_files as $file ) {
			$file = self::add_path_prefix( $file );
			if ( file_exists( $file ) ) {
				require_once $file;
			} else {
				new self( true );
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
			$path = $fileinfo -> getPathname() . '/' . ltrim( $file, '/' );
			if ( is_readable( $path ) ) {
				return $path;
			}
		}
		return false;
	}

	/**
	 * add wp-content directory's path to path string
	 *
	 * @access private
	 *
	 * @param  string $path
	 * @return string
	 */
	private static function add_path_prefix( $path ) {
		return \WP_CONTENT_DIR . $path;
	}

	/**
	 * remove wp-content directory's path from path string
	 *
	 * @access private
	 *
	 * @param  string $path
	 * @return string
	 */
	private static function remove_path_prefix( $path ) {
		static $start = null;
		if ( null === $start ) {
			$start = strlen( \WP_CONTENT_DIR );
		}
		return substr( $path, $start );
	}

}
