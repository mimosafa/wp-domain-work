<?php

namespace WP_Domain_Work\Property;

class post_children extends posts {

	public function __construct( $var, Array $args, $post = 0 ) {
		if ( ! get_post( $post )->ID ) {
			return false;
		}
		add_filter( 'wp_domain_work_posts_property_query_args', [ $this, 'query_args' ], 10, 3 );

		if ( ! parent::__construct( $var, $args, $post ) ) {
			return false;
		}
	}

	public function query_args( $r, $args, $post ) {
		$r['post_parent'] = get_post( $post )->ID;
		if ( ! array_key_exists( 'orderby', $args ) || ! array_key_exists( 'orderby', $r ) ) {
			$r['orderby'] = 'menu_order';
		}
		if ( ! array_key_exists( 'order', $args ) || ! array_key_exists( 'order', $r ) ) {
			$r['order'] = 'ASC';
		}
		return $r;
	}

}
