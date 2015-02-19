<?php

namespace WP_Domain_Work\Admin\list_table;

class posts_list_table {
	use \WP_Domain_Work\Utility\Classname;

	private $domain;
	private $post_type;

	private $properties;

	private $columns          = [];
	private $column_labels    = [];
	private $sortable_columns = [];

	private static $built_in_column_types = [
		'cb', 'title', 'author ', 'categories ', 'tags', 'comments ', 'date'
	];

	public function __construct( $post_type ) {
		if ( ! post_type_exists( $post_type ) ) {
			return;
		}
		$this->post_type = $post_type;
		$this->domain = get_post_type_object( $post_type )->rewrite['slug'];
		$class = sprintf( 'WP_Domain\\%s\\properties', $this->domain );
		$this->properties = $class::get_property_setting();
		$this->init();
	}

	public function add( $column, $args ) {
		$this->columns[] = $column;
		if ( ! is_array( $args ) || ! $args ) {
			return;
		}
		if ( array_key_exists( 'label', $args ) && is_string( $args['label'] ) && $args['label'] ) {
			$this->column_labels[$column] = $args['label'];
		}
		if ( array_key_exists( 'sortable', $args ) && $args['sortable'] === true ) {
			$this->sortable_columns[] = $column;
		}
	}

	/**
	 * @todo ソートのバリエーション
	 */
	private function init() {
		add_filter( 'manage_' . $this->post_type . '_posts_columns', [ $this, 'manage_columns' ] );
		add_filter( 'manage_' . $this->post_type . '_posts_custom_column', [ $this, 'column_callbacks' ], 10, 2 );
		add_filter( 'manage_edit-' . $this->post_type . '_sortable_columns', [ $this, 'manage_sortable_columns' ] );
		add_filter( 'request', [ $this, 'columns_order' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'hide_columns_style'] );
	}

	public function manage_columns( $columns ) {
		if ( empty( $this->columns ) ) {
			return $columns;
		}
		$new_columns = [];
		$new_columns['cb'] = $columns['cb'];
		foreach ( $this->columns as $column_name ) {
			if ( array_key_exists( $column_name, $columns ) ) {
				// Built-in Columns
				$new_columns[$column_name] = ! array_key_exists( $column_name, $this->column_labels ) ? $columns[$column_name] : $this->column_labels[$column_name];
				continue;
			}
			/**
			 * Defined domain's properties Columns
			 */
			if ( ! $prop = $this->_get_property_obj( $column_name ) ) {
				continue;
			}
			$new_columns[$column_name] = $prop->label;
		}
		if ( ! empty( $new_columns ) ) {
			return $new_columns;
		}
		return $columns;
	}

	public function column_callbacks( $column_name, $post_id ) {
		$class = sprintf( 'WP_Domain\\%s\\properties', $this->domain );
		$props = new $class( $post_id );
		$return = $props->$column_name->getValue();
		echo $return;
	}

	public function manage_sortable_columns( $sortable_columns ) {
		if ( ! empty( $this->sortable_columns ) ) {
			foreach ( $this->sortable_columns as $column ) {
				$sortable_columns[$column] = $column;
			}
		}
		return $sortable_columns;
	}

	/**
	 * @see http://hijiriworld.com/web/wordpress-admin-customize/#list
	 */
	public function columns_order( $vars ) {
		if ( empty( $this->sortable_columns ) ) {
			return $vars;
		}
		if ( isset( $vars['orderby'] ) && in_array( $vars['orderby'], $this->sortable_columns ) ) {
			$prop = $this->_get_property_obj( $vars['orderby'] );
			$type = self::getClassName( $prop );
			if ( $type === 'menu_order' ) {
				$vars = array_merge( $vars, [ 'orderby' => 'menu_order' ] );
			} else if ( $type === 'integer' ) {
				$vars = array_merge( $vars, [ 'orderby' => 'meta_value_num', 'meta_key' => $vars['orderby'] ] );
			} else {
				$vars = array_merge( $vars, [ 'orderby' => 'meta_value', 'meta_key' => $vars['orderby'] ] );
			}
		}
		return $vars;
	}

	/**
	 * filter hook 'wp_domain_work_{$domain}_hide_columns'
	 */
	public function hide_columns_style() {
		if ( empty( $this->columns ) ) {
			return;
		}
		$selector = '';
		$columns = apply_filters( 'wp_domain_work_' . $this->domain . '_hide_columns', $this->columns );
		foreach ( $columns as $column ) {
			if ( in_array( $column, self::$built_in_column_types ) ) {
				continue;
			}
			$selector .= '.column-' . $column . ", ";
		}
		$selector = substr( $selector, 0, -2 ) . ' ';
		echo <<<EOF
<style type="text/css">
  @media screen and (max-width: 782px) { {$selector} { display: none; } }
</style>\n
EOF;
	}

	/**
	 *
	 */
	private function _get_property_obj( $property ) {
		if ( ! array_key_exists( $property, $this->properties ) ) {
			return false;
		}
		$propArgs = $this->properties[$property];
		if ( in_array( $property, [ 'menu_order', 'post_parent' ] ) ) {
			$propClass = sprintf( 'WP_Domain_Work\\Property\\%s', $property );
			return new $propClass( 0, $propArgs );
		}
		if ( ! array_key_exists( 'type', $propArgs ) ) {
			if ( ! array_key_exists( 'model', $propArgs ) ) {
				return false;
			}
			$model = $propArgs['model'];
			$propsClassName = sprintf( 'WP_Domain\\%s\\properties', $this->domain );
			$propArgs = array_merge(
				$propsClassName::$defaultPropSettings[$model],
				$propArgs
			);
		}
		if ( ! array_key_exists( 'type', $propArgs ) ) {
			return false;
		}
		if ( in_array( $propArgs['type'], [ 'group', 'set' ] ) ) {
			return false;
		}
		$propClass = sprintf( 'WP_Domain_Work\\Property\\%s', $propArgs['type'] );
		if ( ! class_exists( $propClass ) ) {
			return false;
		}
		return new $propClass( $property, $propArgs );
	}

}
