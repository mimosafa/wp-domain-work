<?php
namespace WPDW\Device\Admin;

class WPDW_List_Table extends \WP_List_Table {

	/**
	 * Constructor
	 *
	 * @access public
	 *
	 * @param  array $args {
	 * 
	 *     @type string $domain
	 *     @type string $name
	 *     @type array  $value
	 *
	 *     @type string $type
	 *     @type string $model
	 *     @type string $label
	 *
	 *     @type array  $query_args
	 *     @type string $field
	 * 
	 * }
	 */
	public function __construct( Array $args ) {
		$const_args = [];
		if ( isset( $args['singular'] ) )
			$const_args['singular'] = (string) filter_var( $args['singular'] );
		$const_args['plural'] = isset( $args['plural'] ) && ( $plural = (string) filter_var( $args['plural'] ) ) ? $plural : $args['name'];

		$const_args = array_merge( $args, $const_args );

		parent::__construct( $const_args );
	}

	/**
	 * Columns
	 *
	 * @access public
	 *
	 * @return array
	 */
	public function get_columns() {
		$columns = [
			'cb' => '<input type="checkbox" />',
			'title'  => $this->_args['label'],
		];
		if ( count( $this->_args['value'] ) > 1 ) {
			$columns = array_merge( [ 'handle' => '<span class="dashicons dashicons-editor-ol"></span>' ], $columns );
		}
		return $columns;
	}

	public function prepare_items() {
		$this->_column_headers = [
			$this->get_columns(),
			$this->get_hidden_columns(),
			$this->get_sortable_columns()
		];
		$this->items = $this->_args['value'];
	}

	/**
	 * @access public
	 *
	 * @return array
	 */
	public function get_hidden_columns() { return []; }
	public function get_sortable_columns() { return []; }

	public function get_bulk_actions() {
		return [ 'edit' => 'Edit' ];
	}

	public function column_default( $item, $column_name ) {
		//return $item[$column_name];
	}

	public function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="%s[]" value="%s">', $this->_args['name'], $this->_args['field'] );
	}

	public function column_handle( $item ) {
		return '<span class="dashicons dashicons-menu"></span>';
	}

	public function column_title( $item ) {
		$actions = [
			'edit' => sprintf( '<a href="%s">Edit</a>', get_edit_post_link( $item->ID ) ),
		];
		return sprintf( '<strong>%s</strong>%s', get_the_title( $item ), $this->row_actions( $actions ) );
	}

	/**
	 *
	 *
	 *
	 */

	protected function display_tablenav( $which ) {
?>
	<div class="tablenav <?php echo esc_attr( $which ); ?>">

		<div class="alignleft actions bulkactions">
			<?php $this->bulk_actions( $which ); ?>
		</div>
<?php
		$this->extra_tablenav( $which );
		$this->pagination( $which );
?>

		<br class="clear" />
	</div>
<?php
	}

	//protected function bulk_actions( $which = '' ) {}

}
