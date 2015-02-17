<?php

namespace WP_Domain_Work\Module;

/**
 * This is 'Trait',
 * must be used in '\(domain)\admin' class.
 *
 * @uses
 */
trait admin {
	use base;

	protected function init() {
		global $pagenow;
		if ( $this->registered === 'post_type' ) {
			if ( $pagenow === 'post.php' || $pagenow === 'post-new.php' ) {
				add_action( 'add_meta_boxes', [ $this, 'post_type_meta_boxes' ] );
			} else if ( $pagenow === 'edit.php' ) {
				$this->post_type_columns();
			}
			\WP_Domain_Work\Admin\post\post_type_supports::init( $this->registeredName );
			new \WP_Domain_Work\WP\admin\save_post( $this->registeredName );
		}
	}

	public function post_type_meta_boxes() {
		if ( ! $props =& $this->_get_properties() ) {
			return;
		}
		if ( isset( $this->meta_boxes ) && is_array( $this->meta_boxes ) && $this->meta_boxes ) {
			foreach ( $this->meta_boxes as $arg ) {
				if ( is_string($arg) && $prop = $props->$arg ) {
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
					\WP_Domain_Work\Admin\meta_boxes\property_meta_box::set( $propName, $prop->getArray() );
				}
			}
		}
	}

	public function post_type_columns() {
		if ( ! isset( $this->columns ) || ! is_array( $this->columns ) || ! $this->columns ) {
			return;
		}
		$_PLT = new \WP_Domain_Work\Admin\list_table\posts_list_table( $this->registeredName );
		foreach ( $this->columns as $column => $args ) {
			$_PLT->add( $column, $args );
		}
	}

}
