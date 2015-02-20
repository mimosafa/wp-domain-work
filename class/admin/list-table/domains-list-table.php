<?php

namespace WP_Domain_Work\Admin\list_table;

/**
 *
 */
class Domains_List_Table extends \WP_List_Table {

	/**
	 * Constructor
	 */
	public function __construct( $args = [] ) {
		parent::__construct( [
			'singular' => 'domain',
			'plural'   => 'domains',
		] );
	}

	public function get_columns() {
		return [
			'cb'       => '<input type="checkbox" />',
			'name'     => 'Name',
			'register' => 'Registered As',
			'status'   => 'Status',
		];
	}

	function get_hidden_columns() {
		return [
			//
		];
	}

	function get_sortable_columns() {
		return [
			'name' => [ 'name', false ],
		];
	}

	public function column_default( $item, $column_name ) {
		return $item[$column_name];
	}

	public function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="domains[]" value="%s" />', strtolower( $item['name'] ) );
	}

	public function column_name( $item ) {
		$actions = [
			'edit'   => sprintf( '<a href="?page=%s&action=edit&domain=%s">Edit</a>',     $_REQUEST['page'], strtolower( $item['name'] ) ),
			'except' => sprintf( '<a href="?page=%s&action=except&domain=%s">Except</a>', $_REQUEST['page'], strtolower( $item['name'] ) ),
		];
		return sprintf( '<strong>%s</strong>%s', esc_html( $item['name'] ), $this->row_actions( $actions ) );
	}

	public function column_register( $item ) {
		//
		return $item['register'];
	}

	public function get_bulk_actions() {
		return [ 'except' => 'Except' ];
	}

	public function process_bulk_action() {
		if( 'except' === $this->current_action() ) {
			wp_die( 'Items deleted (or they would be if we had items to delete)!' );
		}
	}

	public function prepare_items() {
		$this->_column_headers = [
			$this->get_columns(),
			$this->get_hidden_columns(),
			$this->get_sortable_columns()
		];

		$this->process_bulk_action();

		$data = \WP_Domain_Work\Plugin::get_domains();

		$per_page     = -1;
		$current_page = $this->get_pagenum();
		$total_items  = count( $data );

		$this->items = array_slice( $data, ( ( $current_page - 1 ) * $per_page ), $per_page );

		$this->set_pagination_args( [
			'total_items' => $total_items,
			'per_page'    => $per_page,
			'total_pages' => ceil( $total_items / $per_page )
		] );
	}

}
