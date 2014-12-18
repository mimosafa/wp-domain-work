<?php

namespace service\domain;

/**
 * 
 */
class directories {

	/**
	 * @var string
	 */
	private $_domains_dir = 'domains';

	/**
	 * Domains directories in theme directories
	 * 
	 * @var array
	 */
	private $directories = [];

	/**
	 * Array of domain's settings, those have properties.php
	 *
	 * @var array
	 */
	private $domains = [];

	/**
	 * functions files for domain
	 *
	 * @var array file path to functions.php
	 */
	private $functions_files = [];

	/**
	 * Class loaders for domain
	 *
	 * @var array
	 */
	private $classloaders = [];

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
	 * 
	 */
	private static $property_data = [
		'name'          => 'Name',
		'plural_name'   => 'Plural Name',
		'register'      => 'Register As',
		'post_type'     => 'Post Type Name',
		'taxonomy'      => 'Taxonomy Name',
		'taxonomy_type' => 'Taxonomy Type',
		'related_post_type' => 'Related Post Type',
		'rewrite'       => 'Permalink Format',
		'capability_type' => 'Capability Type',
	];

	/**
	 * 
	 */
	public function __construct() {
		$this -> directories[] = get_template_directory()   . '/' . ltrim( $this -> _domains_dir, '/' );
		$this -> directories[] = get_stylesheet_directory() . '/' . ltrim( $this -> _domains_dir, '/' );
		if ( $excepted_domains = get_option( 'wp_dct_excepted_domains' ) ) {
			self::$_excepted = array_merge( $excepted_domains, self::$_excepted );
		}
		$this -> scan();
	}

	/**
	 * Scan the domain directories,
	 * in order from parent theme dirctory to child theme directory
	 */
	private function scan() {

		$done = '';

		/**
		 * Outer loop (parent theme dir -> child theme dir)
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
				 * クラスのオートローダーとヘルパー関数が定義されたファイル (functions.php)
				 * - テーマディレクトリーループ (*1)で 2巡目のみを対象。(子テーマ )
				 * - 親テーマディレクトリーよりも前にしたいため、array_unshiftで配列の先頭に追加する。
				 */
				if ( isset( $this -> classloaders[$domain] ) ) {
					array_unshift( $this -> classloaders[$domain], $path );
					if ( $functions_path = self::returnReadableFilePath( $fileinfo, 'functions.php' ) ) {
						array_unshift( $this -> functions_files, $functions_path );
					}
				}

				/**
				 * properties.phpのヘッダーコメントを読み込み
				 * ファイルが無ければ continue
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

				/**
				 * クラスのオートローダーとヘルパー関数が定義されたファイル (functions.php)
				 * - テーマディレクトリーループ (*1)で 1巡目のみを対象。(親テーマ )
				 */
				if ( !isset( $this -> classloaders[$domain] ) ) {
					$this -> classloaders[$domain][] = $path;
					if ( $functions_path = self::returnReadableFilePath( $fileinfo, 'functions.php' ) ) {
						array_unshift( $this -> functions_files, $functions_path );
					}
				}

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
		if ( 'DirectoryIterator' !== get_class( $fileinfo ) )
			return false;
		$path = $fileinfo -> getPathname() . '/' . ltrim( $file, '/' );
		if ( is_readable( $path ) )
			return $path;
		return false;
	}

	/**
	 * Getter
	 */

	/**
	 * domain arguments
	 * 
	 * @return array
	 */
	public function getDomains() {
		return $this -> domains;
	}

	/**
	 * functions files
	 * 
	 * @return array
	 */
	public function getFunctionsFiles() {
		return $this -> functions_files;
	}

	/**
	 * classloaders
	 * 
	 * @return array
	 */
	public function getClassloaders() {
		return $this -> classloaders;
	}

}
