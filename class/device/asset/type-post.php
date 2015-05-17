<?php
namespace WPDW\Device\Asset;

class type_post extends asset_abstract {
	use asset_vars, asset_models;

	/**
	 * @var array
	 */
	protected $post_type   = [];
	protected $post_status = [ 'publish' ];
	protected $query_args  = [];

	protected $field = 'ID'; // @todo Which field stored

	public function __construct( Array $args ) {
		parent::__construct( $args );
		if ( $this->field !== 'ID' && ( ! $this->post_type || count( $this->post_type ) > 1 ) )
			$this->field = 'ID';
	}

	protected static function arguments_walker( &$arg, $key, $asset ) {
		if ( $key === 'post_type' && isset( $arg ) ) :
			$arg = array_filter( (array) $arg, function( $pt ) {
				return post_type_exists( $pt );
			} );
			$arg = $arg ?: null;
		elseif ( $key === 'field' ) :
			$arg = in_array( $arg, [ 'ID', 'post_name' ], true ) ? $arg : 'ID';
		elseif ( $key === 'post_status' && isset( $arg ) ) :
			// ...yet
		elseif ( $key === 'query_args' && isset( $arg ) ) :
			// ...yet
		else :
			parent::arguments_walker( $arg, $key, $asset );
		endif;
	}

	protected function filter( $value, $post = null ) {
		if ( is_array( $value ) && $this->multiple ) {
			$posts = [];
			foreach ( $value as $val ) {
				if ( $val = get_post( $val ) )
					$posts[] = $val;
			}
			return $posts;
		}
		$value = ! is_array( $value ) ? $value : array_shift( $value );
		return get_post( $value );
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
			return implode( $this->glue, $value );
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
