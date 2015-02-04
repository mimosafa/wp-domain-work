<?php

namespace wordpress\model;

/**
 * @see https://github.com/WordPress/WordPress/blob/master/wp-includes/meta.php
 * @see https://github.com/WordPress/WordPress/blob/master/wp-includes/post.php
 * @see https://github.com/WordPress/WordPress/blob/master/wp-includes/user.php
 */
class metadata {

	/**
	 * @var string
	 */
	protected $meta_type;

	/**
	 * @var int
	 */
	protected $object_id;

	/**
	 * Constructor
	 *
	 * @param object|int $object
	 * @param string $type
	 */
	public function __construct( $object, $type = null ) {

		if ( is_object( $object ) ) {

			$class = get_class( $object );

			if ( 'WP_Post' === $class ) {

				$this -> meta_type = 'post';
				$this -> object_id = $object -> ID;

			} else if ( 'WP_User' === $class ) {

				$this -> meta_type = 'user';
				$this -> object_id = $object -> ID;

			} else {

				// 'comment', & new custom ... yet!

			}

		} else if ( ( $object_id = absint( $object ) ) && is_string( $type ) ) {

			if ( 'post' === $type ) {

				if ( !$post_id = wp_is_post_revision( $object_id ) ) {
					$post_id = $object_id;
				}

				$this -> meta_type = 'post';
				$this -> object_id = $post_id;

			} else if ( 'user' === $type ) {

				if ( get_user_by( 'id', $object_id ) ) {
					$this -> meta_type = 'user';
					$this -> object_id = $object_id;
				}

			} else {

				// 'comment', & new custom ... yet!

			}

		} else {

			// throw error

		}

		if ( is_null( $this -> meta_type ) ) {
			// throw error
		}

	}

	/**
	 * Overloading methods, __isset, __get, __set and __unset.
	 */

	/**
	 * isset( $this -> name );
	 *
	 * @return bool
	 */
	public function __isset( $name ) {
		return metadata_exists( $this -> meta_type, $this -> object_id, $name );
	}

	/**
	 * $this -> name;
	 *
	 * @return mixed
	 */
	public function __get( $name ) {
		$value = $this -> get( $name );

		if ( false === $value ) {
			return false; // throw error: invalid
		}
		if ( empty( $value ) ) {
			return false; // throw error: not exists
		}

		return 1 === count( $value ) ? $value[0] : $value;
	}

	/**
	 * $this -> name = 'value';
	 *
	 * @uses  \utility\is_vector
	 *
	 * @return void
	 */
	public function __set( $name, $value ) {

		if ( \utility\is_vector( $value ) && 1 < count( $value ) ) {
			foreach ( $value as $val ) {
				$this -> add( $name, $val );
			}
		} else {
			$this -> update( $name, $value );
		}

	}

	/**
	 * unset( $this -> name );
	 * - Delete all entries with the specified meta_key.
	 *
	 * @return void
	 */
	public function __unset( $name ) {
		if ( !isset( $this -> $name ) ) {
			return false; // throw error: cannot unset metadata, not exists.
		}
		return $this -> delete( $name );
	}

	/**
	 * Default functions - add, update, delete and get.
	 *
	 * @see https://github.com/WordPress/WordPress/blob/master/wp-includes/meta.php
	 */

	/**
	 * Add metadata.
	 *
	 * @see http://codex.wordpress.org/Function_Reference/add_metadata
	 */
	public function add( $meta_key, $meta_value, $unique = false ) {
		return add_metadata( $this -> meta_type, $this -> object_id, $meta_key, $meta_value, $unique );
	}

	/**
	 * Update metadata.
	 *
	 * @see http://codex.wordpress.org/Function_Reference/update_metadata
	 */
	public function update( $meta_key, $meta_value, $prev_value = '' ) {
		return update_metadata( $this -> meta_type, $this -> object_id, $meta_key, $meta_value, $prev_value );
	}

	/**
	 * Delete metadata.
	 *
	 * @see http://codex.wordpress.org/Function_Reference/delete_metadata
	 */
	public function delete( $meta_key, $meta_value = '' ) {
		return delete_metadata( $this -> meta_type, $this -> object_id, $meta_key, $meta_value );
	}

	/**
	 * Get metadata.
	 *
	 * @see http://codex.wordpress.org/Function_Reference/get_metadata
	 */
	public function get( $meta_key, $single = false ) {
		$value = get_metadata( $this -> meta_type, $this -> object_id, $meta_key, $single );
		if ( $single === false && count( $value ) === 1 && $value === array_values( $value ) ) {
			return $value[0];
		}
		return $value ? $value : false;
		# return get_metadata( $this -> meta_type, $this -> object_id, $meta_key, $single );
	}

}
