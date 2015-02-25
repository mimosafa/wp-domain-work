<?php

namespace WP_Domain_Work\Module;

/**
 * This is 'Trait',
 * must be used in '\(domain)\admin' class.
 *
 * << Class properties >>
 * @var array $meta_boxes
 * @var array $columns
 *
 */
trait admin {
	use base;

	public static function init() {
		$self = new self();
		global $pagenow;
		if ( $self->registered === 'post_type' ) {
			if ( $pagenow === 'post.php' || $pagenow === 'post-new.php' ) {
				add_action( 'add_meta_boxes', [ $self, 'post_type_meta_boxes' ] );
			} else if ( $pagenow === 'edit.php' ) {
				$self->post_type_columns();
			}
			\WP_Domain_Work\Admin\post\post_type_supports::init( $self->registeredName );
			new \WP_Domain_Work\Post\save_post( $self->registeredName );
		}
	}

	public function post_type_meta_boxes() {
		if ( ! $props =& $this->_get_properties() ) {
			return;
		}
		if ( isset( $this->meta_boxes ) && is_array( $this->meta_boxes ) && $this->meta_boxes ) {
			foreach ( $this->meta_boxes as $arg ) {
				if ( is_string( $arg ) && $prop = $props->$arg ) {
					if ( in_array( $arg, [ 'post_parent', 'menu_order' ] ) ) {
						\WP_Domain_Work\Admin\meta_boxes\attributes_meta_box::set( $arg, $prop->getArray() );
					} else {
						//
					}
				} else if ( is_array( $arg ) ) {
					if ( ! array_key_exists( 'property', $arg ) ) {
						continue;
					}
					$propName = $arg['property'];
					if ( ! $prop = $props->$propName ) {
						continue;
					}
					unset( $arg['property'] );
					$metabox = wp_parse_args( $arg, $prop->getArray() );
					\WP_Domain_Work\Admin\meta_boxes\property_meta_box::set( $propName, $metabox );
				}
			}
		}
	}

	public function post_type_columns() {
		if ( ! isset( $this->columns ) || ! is_array( $this->columns ) || ! $this->columns ) {
			return;
		}
		$_PLT = new \WP_Domain_Work\Admin\list_table\posts_admin_columns( $this->registeredName );
		foreach ( $this->columns as $column => $args ) {
			$_PLT->add( $column, $args );
		}
	}

}
