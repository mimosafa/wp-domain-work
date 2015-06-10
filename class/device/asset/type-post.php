<?php
namespace WPDW\Device\Asset;

class type_post extends asset_simple {
	use asset_trait, Model\meta_post_meta;

	/**
	 * @var string
	 */
	protected $context;

	/**
	 * @var array
	 */
	protected $post_type   = [];
	protected $post_status = [ 'publish' ];
	protected $query_args  = [];

	protected $field = 'ID'; // @todo Which field stored

	/**
	 * Constructor
	 *
	 * @access public
	 *
	 * @uses   WPDW\Device\Asset\asset_simple::__construct()
	 *
	 * @param  array $args
	 * @return (void)
	 */
	public function __construct( Array $args ) {
		parent::__construct( $args );
		if ( $this->field !== 'ID' && ( ! $this->post_type || count( $this->post_type ) > 1 ) )
			$this->field = 'ID';
		if ( $this->post_type )
			$this->query_args = array_merge( $this->query_args, [ 'post_type' => $this->post_type ] );
		if ( $this->post_status )
			$this->query_args = array_merge( $this->query_args, [ 'post_status' => $this->post_status ] );
	}

	/**
	 * @access protected
	 *
	 * @uses   WPDW\Device\Asset\asset_simple::arguments_walker()
	 *
	 * @param  mixed &$arg
	 * @param  string $key
	 * @param  string $asset
	 * @return (void)
	 */
	protected static function arguments_walker( &$arg, $key, $asset ) {
		if ( $key === 'context' ) :
			/**
			 * @var string $context
			 */
			$arg = in_array( $arg, [ 'post_children', ], true ) ? $arg : null;
		elseif ( $key === 'post_type' && isset( $arg ) ) :
			/**
			 * @var array $post_type
			 */
			$arg = array_filter( (array) $arg, function( $pt ) {
				return post_type_exists( $pt );
			} );
			$arg = $arg ?: null;
		elseif ( $key === 'field' ) :
			/**
			 * @var string $field
			 */
			$arg = in_array( $arg, [ 'ID', 'post_name' ], true ) ? $arg : 'ID';
		elseif ( $key === 'post_status' && isset( $arg ) ) :
			// ...yet
		elseif ( $key === 'query_args' ) :
			// ...yet
		else :
			parent::arguments_walker( $arg, $key, $asset );
		endif;
	}

	/**
	 * @access public
	 *
	 * @param  mixed $value
	 * @return mixed
	 */
	public function filter_singular( $value ) {
		if ( is_object( $value ) && get_class( $value ) === 'WP_Post' ) :
			$post = $value;
		else :
			if ( ! $value = filter_var( $value ) )
				return null;

			if ( $this->field === 'ID' && $value = absint( $value ) )
				$post = get_post( $value );
			else if ( $this->field === 'post_name' && filter_var( $value ) )
				$post = get_page_by_path( $value, 'OBJECT', \WPDW\_alias( $this->domain ) );
			else
				return null;
		endif;

		/**
		 * @todo WP_Post object validation
		 */

		return $post;
	}

	/**
	 * Overwrite WPDW\Device\Asset\asset_simple::filter_input()
	 *
	 * @access public
	 *
	 * @param  mixed $value
	 * @return int|string|null
	 */
	public function filter_input( $value ) {
		$value = $this->filter( $value );
		if ( ! isset( $value ) )
			return null;
		if ( $this->multiple && is_array( $value ) ) :
			$return = [];
			foreach ( $value as $post )
				$return[] = $this->filter_input( $post );
			return $return;
		else :
			return $value->{$this->field};
		endif;
	}

	/**
	 * Return value for printing in list table column
	 *
	 * @access public
	 *
	 * - Hooked on '_wpdw_{$name}_column'
	 * @see  WPDW\Device\Admin\posts_column::column_callback()
	 *
	 * @param  mixed $value
	 * @param  int   $post_id (Non use)
	 * @return string
	 */
	public function print_column( $value, $post_id ) {
		if ( ! isset( $value ) )
			return;
		if ( is_array( $value ) ) {
			array_walk( $value, function( &$post ) {
				$post = $this->_print_in_admin( $post );
			} );
			return implode( $this->delimiter, $value );
		}
		return $this->_print_in_admin( $value );
	}

	/**
	 * Return value for printing in admin
	 *
	 * @access protected
	 *
	 * @see    https://github.com/WordPress/WordPress/blob/4.2-branch/wp-admin/includes/class-wp-posts-list-table.php#L731
	 *
	 * @param  WP_Post $post
	 * @return string
	 */
	protected function _print_in_admin( \WP_Post $post ) {
		$before = '';
		$after  = '';
		$title = esc_attr( get_the_title( $post ) );
		if ( current_user_can( 'edit_post', $post ) && filter_input( \INPUT_GET, 'post_status' ) !== 'trash' ) {
			$edit_link = get_edit_post_link( $post );
			$before .= '<a href="' . $edit_link . '" title="' . esc_attr( sprintf( __( 'Edit &#8220;%s&#8221;' ), $title ) ) . '">';
			$after  .= '</a>';
		}
		return $before . apply_filters( 'the_title', $title ) . $after;
	}

}
