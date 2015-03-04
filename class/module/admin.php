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

	public static function init( Array $args ) {
		$self = new self( $args );
		if ( $self->registered === 'post_type' ) {
			$self->init_post_type();
		} else if ( $self->registered === 'taxonomy' ) {
			// $self->init_taxonomy();
		}
	}

	private function init_post_type() {
		global $pagenow;
		if ( in_array( $pagenow, [ 'post.php', 'post-new.php' ] ) ) {
			if ( property_exists( $this, 'advanced_forms' ) && is_array( $this->advanced_forms ) && $this->advanced_forms ) {
				$this->add_advanced_forms();
			}
			if ( property_exists( $this, 'meta_boxes' ) && is_array( $this->meta_boxes ) && $this->meta_boxes ) {
				add_action( 'add_meta_boxes', [ $this, 'post_type_meta_boxes' ] );
			}
		} else if ( $pagenow === 'edit.php' ) {
			if ( property_exists( $this, 'columns' ) && is_array( $this->columns ) && $this->columns ) {
	 			$this->post_type_columns();
			}
		}
		#\WP_Domain_Work\Admin\post\post_type_supports::init( $self->registeredName );
		new \WP_Domain_Work\Post\save_post( $this->registeredName );
	}

	private function add_advanced_forms() {
		foreach ( $this->advanced_forms as $hook => $args ) {
			if ( ! $args ) {
				continue;
			}
			/**
			 * 何かが違う…
			 */
			$method = 'set_' . $hook;
			foreach ( $args as $array ) {
				\WP_Domain_Work\Admin\edit_form_advanced::$method( $array );
			}
		}
	}

	public function post_type_meta_boxes() {
		if ( ! $props =& $this->_get_properties() ) {
			return;
		}
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

	public function post_type_columns() {
		$_PLT = new \WP_Domain_Work\Admin\list_table\posts_admin_columns( $this->registeredName );
		foreach ( $this->columns as $column => $args ) {
			$_PLT->add( $column, $args );
		}
	}

}
