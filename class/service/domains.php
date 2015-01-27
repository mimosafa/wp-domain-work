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
	private $classloaders = [];

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
		if ( $force_scan || !\WP_Domain_Work::get_option_value( 'registered_domains' ) ) {
			// wp-content/domains
			$this -> directories[] = \WP_CONTENT_DIR . '/' . self::DOMAINS_DIR_NAME;
			// wp-content/themes/your-parent-theme/domains
			$this -> directories[] = get_template_directory()   . '/' . self::DOMAINS_DIR_NAME;
			// wp-content/themes/your-child-theme/domains
			$this -> directories[] = get_stylesheet_directory() . '/' . self::DOMAINS_DIR_NAME;
			if ( $excepted_domains = \WP_Domain_Work::get_option_value( 'excepted_domains' ) ) {
				self::$_excepted = array_merge( $excepted_domains, self::$_excepted );
			}
			$this -> scan_directories();
		}
	}

	/**
	 */
	private function scan_directories() {
		$done = '';

		/**
		 * Outer loop (wp-content/domains -> parent theme dir -> child theme dir)
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

				// *1

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
					array_merge( $this -> domains[$domain], $property );
				}

				// *2

			}

			$done = $path;

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

}
