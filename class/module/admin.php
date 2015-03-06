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

	//private static $wrote_property = [];

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
				add_action( 'dbx_post_advanced', [ $this, 'add_advanced_forms' ] );
			}
			if ( property_exists( $this, '_meta_boxes' ) && is_array( $this->_meta_boxes ) && $this->_meta_boxes ) {
				add_action( 'add_meta_boxes', [ $this, 'post_type_meta_boxes' ] );
			}
			add_action( 'edit_form_top', [ $this, 'show_post_parent' ] );
			add_action( 'admin_enqueue_scripts', function() {
				wp_enqueue_style( 'wp-dw-post' );
			} );
		} else if ( $pagenow === 'edit.php' ) {
			if ( property_exists( $this, 'columns' ) && is_array( $this->columns ) && $this->columns ) {
	 			$this->post_type_columns();
			}
		}
		#\WP_Domain_Work\Admin\post\post_type_supports::init( $self->registeredName );
		new \WP_Domain_Work\Post\save_post( $this->registeredName );
	}

	public function add_advanced_forms() {
		foreach ( $this->advanced_forms as $hook => $array ) {
			if ( ! $array ) {
				continue;
			}
			foreach ( $array as $args ) {
				if ( array_key_exists( 'property', $args ) && $prop = $this->_get_property( $args['property'] ) ) {
					$args = array_merge( $prop->getArray(), $args );
				}
				\WP_Domain_Work\Admin\edit_form_advanced::set( $hook, $args );
			}
			add_action( 'admin_enqueue_scripts', function() {
				wp_enqueue_script( 'wp-dw-children-list-table' );
			} );
		}
	}

	public function post_type_meta_boxes() {
		if ( ! $props =& $this->_get_properties() ) {
			return;
		}
		foreach ( $this->_meta_boxes as $arg ) {
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

	public function show_post_parent( $post ) {
		global $pagenow;
		if ( $pagenow === 'post.php' ) {
			if ( $post->post_parent && $parent = $this->_get_property( 'post_parent' ) ) {
				$label = esc_html( $parent->label );
				if ( $parent->value ) {
					$parent_name = sprintf(
						'<a href="%s">%s</a>',
						esc_url( get_edit_post_link( $parent->value ) ),
						esc_html( get_the_title( $parent->value ) )
					);
				} else {
					$parent_name = 'None';
				}
				echo "<p class=\"description\"><strong>{$label}: </strong>{$parent_name}\n</p>";
			}
		} else if ( array_key_exists( 'post_parent', $_REQUEST ) && absint( $_REQUEST['post_parent'] ) ) { // post-new.php
			/**
			 * 
			 */
			if ( ! $parent = get_post( $_REQUEST['post_parent'] ) ) {
				return;
			}
			$parent_post_type = $parent->post_type;
			if ( ! $propsClass =& $this->_get_properties() ) {
				return;
			}
			if (
				( ! $parent_settings = $propsClass::get_property_setting( 'post_parent' ) )
				|| ! array_key_exists( 'post_type', $parent_settings )
				|| ! in_array( $parent_post_type, (array) $parent_settings['post_type'] )
			) {
				return;
			}
			add_action( 'edit_form_after_editor', function() {
				remove_meta_box( $this->registeredName . 'parentdiv', $this->registeredName, 'side' );
			} );
			$text = sprintf(
				__( 'This %s will be added as %s, <a href="%s">%s</a>\'s child post.' ),
				get_post_type_object( $this->registeredName )->label,
				get_post_type_object( $parent_post_type )->label,
				esc_url( get_edit_post_link( $parent ) ),
				esc_html( get_the_title( $parent ) )
			);
			echo $text;
			echo '<input type="hidden" name="parent_id" value="' . esc_attr( $_REQUEST['post_parent'] ) . '" />';
		}
	}

	protected function _get_property( $property ) {
		if ( ! $properties =& $this->_get_properties() ) {
			return null;
		}
		return $properties->$property;
	}

}
