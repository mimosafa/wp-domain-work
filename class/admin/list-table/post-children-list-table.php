<?php

namespace WP_Domain_Work\Admin\list_table;

class Post_Children_List_Table extends \WP_List_Table {

	protected $defaults = [
		'plural'   => '',
		'singular' => '',
		'ajax'     => false,
		'screen'   => null,
	];

	public function __construct( Array $args ) {
		if ( ! array_key_exists( 'plural', $args ) || ! is_string( $args['plural'] ) || ! $args['plural'] ) {
			return;
		}
		$args = wp_parse_args( $args, $this->defaults );

		parent::__construct( $args );
		/*
		echo '<pre>';
		var_dump( $this );
		echo '</pre>';*/
	}

	protected function set_items() {
		$children = $this->_args['value'];
		foreach ( $children as $child ) {
			$this->items[] = [
				'ID' => $child->ID,
				'title' => get_the_title( $child ),
			];
		}
	}

	public function get_columns() {
		return [
			'cb'       => '<input type="checkbox" />',
			'title'    => 'Title',
			/*
			'register' => 'Registered As',
			'status'   => 'Status',
			*/
		];
	}

	function get_hidden_columns() {
		return [
			//
		];
	}

	function get_sortable_columns() {
		return [
			//'name' => [ 'name', false ],
		];
	}

	public function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="%s[]" value="%s" />', $this->_args['singular'], $item['ID'] );
	}

	public function column_title( $item ) {
		$actions = [
			'edit'   => sprintf( '<a href="?post=%s&action=edit&id=%s">Edit</a>',     $_REQUEST['post'], $item['ID'] ),
			'except' => sprintf( '<a href="?post=%s&action=except&id=%s">Except</a>', $_REQUEST['post'], $item['ID'] ),
		];
		return sprintf( '<strong>%s</strong>%s', esc_html( $item['title'] ), $this->row_actions( $actions ) );
	}

	public function prepare_items() {
		$this->_column_headers = [
			$this->get_columns(),
			$this->get_hidden_columns(),
			$this->get_sortable_columns()
		];
		$this->set_items();
		//var_dump($this->items); die();

		//$this->process_bulk_action();
/*
		$data = \WP_Domain_Work\Plugin::get_domains();
**/
		$per_page     = -1;
		$current_page = $this->get_pagenum();
		$total_items  = count( $this->items );

		#$this->items = array_slice( $this->items, ( ( $current_page - 1 ) * $per_page ), $per_page );

		$this->set_pagination_args( [
			'total_items' => $total_items,
			'per_page'    => $per_page,
			'total_pages' => ceil( $total_items / $per_page )
		] );

	}

}
