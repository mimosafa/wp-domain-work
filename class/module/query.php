<?php

namespace WP_Domain_Work\Module;

/**
 * This is 'Trait',
 * must be used in '\(domain)\query' class.
 *
 * @uses
 */
trait query {

	protected function __construct() {
		/**
		 * Custom init method defined each domains
		 */
		if ( method_exists( $this, 'prepare' ) ) {
			$this->prepare();
		}
	}

	public static function init() {
		$self = new self();
		/**
		 * Hide in front-end
		 */
		if ( ! is_admin() && property_exists( $self, 'private_in_frontend' ) && $self->private_in_frontend === true ) {
			$self->forbidden();
		}
		/**
		 * Main query
		 */
		if ( property_exists( $self, 'query_args' ) && is_array( $self->query_args ) ) {
			add_action( 'pre_get_posts', [ $self, 'main_query' ], 10 );
		}
	}

	/**
	 *
	 */
	public function main_query( $query ) {
		if ( ! $query->is_main_query() || $query->is_singular ) {
			return;
		}

		if ( $query->is_admin ) {
			/**
			 * 管理画面(edit.php)でカラムでソートを掛けた際に並び替えがされない不具合を解消。
			 *
			 * @see WP_Domain_Work\Admin\list_table\posts_list_table::columns_order( $vars )
			 */
			if ( array_key_exists( 'order', $this->query_args ) && array_key_exists( 'order', $query->query ) ) {
				if ( strtolower( $query->query['order'] ) !== strtolower( $this->query_args['order'] ) ) {
					unset( $this->query_args['order'] );
				}
			}
			if ( array_key_exists( 'orderby', $this->query_args ) && array_key_exists( 'orderby', $query->query ) ) {
				if ( $query->query['orderby'] !== $this->query_args['orderby'] ) {
					unset( $this->query_args['orderby'] );
				} else if ( in_array( $query->query['orderby'], [ 'meta_value', 'meta_value_num' ] ) ) {
					// meta_key should be unset...
				}
			}

			/**
			 * 自分以外のポストの編集権限がない人
			 */
			if ( property_exists( $this, 'filter_others') && $this->filter_others === true ) {
				if ( ! current_user_can( 'edit_others_posts', $query->query_vars['post_type'] ) ) {
					$user_id = get_current_user_id();
					$this->query_args['author'] = $user_id;
				}
			}
		}

		foreach ( $this->query_args as $key => $val ) {
			$query->set( $key, $val );
		}
	}

	/**
	 * Force 403 forbidden for not permitted user.
	 */
	private function forbidden() {
		//status_header( 403 );
		header( 'HTTP/1.1 403 Forbidden' );
		echo '<h1>403 Forbidden</h1>';
		die();
	}

}
