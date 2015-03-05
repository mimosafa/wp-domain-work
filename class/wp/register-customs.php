<?php

namespace WP_Domain_Work\WP;

/**
 * WordPress custom post type & custom taxonomy settings wrapper class
 *
 * @uses \singleton
 */
class register_customs {
	use \WP_Domain_Work\Utility\Singleton;

	/**
	 * @var array
	 */
	private static $_post_types = [];

	/**
	 * @var array
	 */
	private static $_taxonomies = [];

	/**
	 */
	protected function __construct() {
		$this->init();
	}

	/**
	 * @access public
	 */
	private function init() {
		add_action( 'init', [ $this, 'register_taxonomy'  ], 1 );
		add_action( 'init', [ $this, 'register_post_type' ], 1 );
	}

	/**
	 * @access public
	 *
	 * @param  string $post_type
	 * @param  string $label
	 * @param  array $options
	 * @return (void)
	 */
	public static function add_post_type( $post_type, $label, $options = [] ) {
		$cpt = [
			'post_type' => $post_type,
			'label'     => $label,
			'options'   => $options
		];
		$self = self::getInstance();
		$self::$_post_types[] = $cpt;
	}

	/**
	 * @access public
	 *
	 * @param  string $post_type
	 * @param  string $label
	 * @param  array $options
	 * @return (void)
	 */
	public static function add_taxonomy( $taxonomy, $label, $post_types = [], $options = [] ) {
		$ct = [
			'taxonomy'   => $taxonomy,
			'label'      => $label,
			'post_types' => $post_types,
			'options'    => $options
		];
		$self = self::getInstance();
		$self::$_taxonomies[] = $ct;
	}

	/**
	 * Callback function registring post type
	 * - action hook: 'init'
	 *
	 * @access public
	 */
	public function register_post_type() {
		if ( empty( self::$_post_types ) ) {
			return;
		}
		foreach ( self::$_post_types as $pt ) {
			$pt['options']['label'] = $pt['label'];
			if ( ! empty( $this->_taxonomies ) ) {
				$taxonomies = [];
				foreach ( $this->_taxonomies as $tax ) {
					if ( in_array( $pt['post_type'], $tax['post_types'] ) )
						$taxonomies[] = $tax['taxonomy'];
				}
				if ( ! empty( $taxonomies ) ) {
					$pt['options'] = array_merge(
						$pt['options'],
						[ 'taxonomies' => $taxonomies ]
					);
				}
			}
			$pt['options']['labels'] = $this->post_type_labels( $pt['post_type'], $pt['label'] );
			\register_post_type( $pt['post_type'], $pt['options'] );
		}
	}

	private function post_type_labels( $post_type, $label, $textdomain = '' ) {
		return [
			'name'               => _x( $label, 'post type general name', $textdomain ),
			'singular_name'      => _x( $label, 'post type singular name', $textdomain ),
			'menu_name'          => _x( $label, 'admin menu', $textdomain ),
			'name_admin_bar'     => _x( $label, 'add new on admin bar', $textdomain ),
			'add_new'            => _x( 'Add New', $post_type, $textdomain ),
			'add_new_item'       => sprintf( __( 'Add New %s', $textdomain ), $label ),
			'new_item'           => sprintf( __( 'New %s', $textdomain ), $label ),
			'edit_item'          => sprintf( __( 'Edit %s', $textdomain ), $label ),
			'view_item'          => sprintf( __( 'View %s', $textdomain ), $label ),
			'all_items'          => sprintf( __( 'All %s', $textdomain ), $label ),
			'search_items'       => sprintf( __( 'Search %s', $textdomain ), $label ),
			'parent_item_colon'  => sprintf( __( 'Parent %s:', $textdomain ), $label ),
			'not_found'          => sprintf( __( 'No %s found.', $textdomain ), $label ),
			'not_found_in_trash' => sprintf( __( 'No %s found in Trash.', $textdomain ), $label ),
		];
	}

	/**
	 * Callback function registring post type
	 * - action hook: 'init'
	 *
	 * @access public
	 */
	public function register_taxonomy() {
		if ( empty( self::$_taxonomies ) ) {
			return;
		}
		foreach ( self::$_taxonomies as $tx ) {
			$tx['options']['label'] = $tx['label'];
			\register_taxonomy( $tx['taxonomy'], $tx['post_types'], $tx['options'] );
		}
	}

}
