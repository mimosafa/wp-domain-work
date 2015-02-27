<?php

namespace WP_Domain_Work\Admin\list_table;

class Post_Children_List_Table extends \WP_List_Table {

	protected $query_args;
	protected $data;

	protected $_actions = [ 'remove' => 'Remove', ];

	public function __construct( Array $args ) {
		/*
		$config = [
			'singular' => $args['singular'],
			'plural'   => $args['plural'],
		];
		*/
		parent::__construct( $args );

		$this->query_args = $args['query_args'];
		$this->data = $args['value'];
		/*
		$post_type = $this->screen->post_type;
		$post_type_object = get_post_type_object( $post_type );
		if ( !current_user_can( $post_type_object->cap->edit_others_posts ) ) {
			$exclude_states = get_post_stati( array( 'show_in_admin_all_list' => false ) );
			$this->user_posts_count = $wpdb->get_var( $wpdb->prepare( "
				SELECT COUNT( 1 ) FROM $wpdb->posts
				WHERE post_type = %s AND post_status NOT IN ( '" . implode( "','", $exclude_states ) . "' )
				AND post_author = %d
			", $post_type, get_current_user_id() ) );
			if ( $this->user_posts_count && empty( $_REQUEST['post_status'] ) && empty( $_REQUEST['all_posts'] ) && empty( $_REQUEST['author'] ) && empty( $_REQUEST['show_sticky'] ) )
				$_GET['author'] = get_current_user_id();
		}
		if ( 'post' == $post_type && $sticky_posts = get_option( 'sticky_posts' ) ) {
			$sticky_posts = implode( ', ', array_map( 'absint', (array) $sticky_posts ) );
			$this->sticky_posts_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT( 1 ) FROM $wpdb->posts WHERE post_type = %s AND post_status NOT IN ('trash', 'auto-draft') AND ID IN ($sticky_posts)", $post_type ) );
		}
		*/
	}

	public function get_columns() {
		return [
			'cb'         => '<input type="checkbox" />',
			'title'      => 'Title',
		];
	}

	function get_hidden_columns() {
		return [
			//
		];
	}

	function get_sortable_columns() {
		return [
			//
		];
	}

	public function get_bulk_actions() {
		return [ 'edit' => 'Edit' ];
	}

	public function column_default( $item, $column_name ) {
		return $item[$column_name];
	}

	public function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="%s[]" value="%s" />', $this->_args['singular'], $item['ID'] );
	}

	public function column_title( $item ) {
		$actions = [
			'edit' => sprintf( '<a href="%s">Edit</a>', get_edit_post_link( $item['ID'] ) ),
		];
		return sprintf( '<strong>%s</strong>%s', esc_html( $item['title'] ), $this->row_actions( $actions ) );
	}

	public function prepare_items() {
		$this->_column_headers = [
			$this->get_columns(),
			$this->get_hidden_columns(),
			$this->get_sortable_columns()
		];
		#var_dump( $this->_args ); die();
		$children = $this->_args['value'];
		foreach ( $children as $child ) {
			$this->items[] = [
				'ID'         => $child->ID,
				'menu_order' => (integer) $child->menu_order,
				'title'      => get_the_title( $child ),
			];
		}
		/*
		global $avail_post_stati, $wp_query, $per_page, $mode;
		$avail_post_stati = wp_edit_posts_query();
		$this->hierarchical_display = ( is_post_type_hierarchical( $this->screen->post_type ) && 'menu_order title' == $wp_query->query['orderby'] );
		$total_items = $this->hierarchical_display ? $wp_query->post_count : $wp_query->found_posts;
		$post_type = $this->screen->post_type;
		$per_page = $this->get_items_per_page( 'edit_' . $post_type . '_per_page' );

 		$per_page = apply_filters( 'edit_posts_per_page', $per_page, $post_type );
		if ( $this->hierarchical_display )
			$total_pages = ceil( $total_items / $per_page );
		else
			$total_pages = $wp_query->max_num_pages;
		if ( ! empty( $_REQUEST['mode'] ) ) {
			$mode = $_REQUEST['mode'] == 'excerpt' ? 'excerpt' : 'list';
			set_user_setting ( 'posts_list_mode', $mode );
		} else {
			$mode = get_user_setting ( 'posts_list_mode', 'list' );
		}
		$this->is_trash = isset( $_REQUEST['post_status'] ) && $_REQUEST['post_status'] == 'trash';
		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'total_pages' => $total_pages,
			'per_page' => $per_page
		) );
		*/
	}

	protected function get_views() {
		/**
		 * @see WP_Domain_Work\Module\status::get_stati
		 */
		$pts = $this->query_args['post_type'];
		$stati = [];
		foreach ( $pts as $pt ) {
			$domain = get_post_type_object( $pt )->rewrite['slug'];
			$cl = "WP_Domain\\{$domain}\\status";
			if ( class_exists( $cl ) ) {
				$stati = $cl::get_stati( [ 'show_in_admin_status_list' => true ] );
			}
		}
		echo '<pre>'; var_dump( $stati ); echo '</pre>'; die();
	}

	//

	/**
	 * Generate the table navigation above or below the table
	 *
	 * @since 3.1.0
	 * @access protected
	 * @param string $which
	 */
	protected function display_tablenav( $which ) {
		if ( 'top' == $which ) {
			// wp_nonce_field( 'bulk-' . $this->_args['plural'] );
?>
	<div class="tablenav <?php echo esc_attr( $which ); ?>">

		<div class="alignleft actions bulkactions">
			<?php $this->bulk_actions( $which ); ?>
		</div>

		<div class="alignleft actions">
			<input type="button" name="" id="addnewchild" class="button action" value="<?php _e( 'Add New' ); ?>">
		</div>
<?php
		$this->extra_tablenav( $which );
		$this->pagination( $which );
?>

		<br class="clear" />
	</div>
<?php
		}
	}

}

/*
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
	}

	protected function set_items() {
		$children = $this->_args['value'];
		foreach ( $children as $child ) {
			$this->items[] = [
				'ID'         => $child->ID,
				'menu_order' => (integer) $child->menu_order,
				'title'      => get_the_title( $child ),
			];
		}
	}

	public function get_columns() {
		return [
			'cb'         => '<input type="checkbox" />',
			'menu_order' => 'Order',
			'title'      => 'Title',
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

	public function column_default( $item, $column_name ) {
		return $item[$column_name];
	}

	public function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="%s[]" value="%s" />', $this->_args['singular'], $item['ID'] );
	}

	public function column_title( $item ) {
		$actions = [
			'edit' => sprintf( '<a href="%s">Edit</a>', get_edit_post_link( $item['ID'] ) ),
		];
		return sprintf( '<strong>%s</strong>%s', esc_html( $item['title'] ), $this->row_actions( $actions ) );
	}

	public function get_bulk_actions() {
		return [ 'edit' => 'Edit' ];
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
*/
