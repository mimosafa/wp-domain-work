<?php

namespace WP_Domain_Work\Module;

trait status {
	use base;

	private static $builtin_statuses = [
		'publish', 'future', 'draft', 'pending', 'private', 'trash'
	];

	private static $customs_defaults = [
		'label'         => '',
		'public'        => false,
		'show_in_admin' => true, // original key for this class
		// 'exclude_from_search'       => true,
		// 'show_in_admin_all_list'    => false,
		// 'show_in_admin_status_list' => true,
		// 'label_count'               => []
	];

	public static function init( Array $args ) {
		$self = new self( $args );
		if ( property_exists( $self, 'builtin' ) && is_array( $self->builtin ) && $self->builtin ) {
			$self->init_builtin_statuses();
		}
		if ( property_exists( $self, 'custom' ) && is_array( $self->custom ) && $self->custom ) {
			$self->init_custom_statuses();
		}
	}

	private function init_builtin_statuses() {
		foreach ( $this->builtin as $status => $args ) {
			if ( ! in_array( $status, self::$builtin_statuses ) ) {
				continue;
			}
			$class = sprintf( 'WP_Domain_Work\\WP\\post\\post_status\\%s', $status );
			if ( class_exists( $class ) ) {
				new $class( $args );
			}
		}
	}

	private function init_custom_statuses() {
		$array = $this->custom_stati_args();
		foreach ( $array as $status => $args ) {
			\WP_Domain_Work\WP\post\post_status\custom_post_status::set( $status, $args );
		}
	}

	private function custom_stati_args( $output = 'array' ) {
		$stati = [];
		if ( property_exists( $this, 'custom' ) || ! $this->custom ) {
			foreach ( $this->custom as $status => $args ) {
				if ( ! is_string( $status ) || ! $status || in_array( $status, self::$builtin_statuses ) ) {
					continue;
				}
				$args = wp_parse_args( $args, self::$customs_defaults );
				if ( ! $args['label'] ) {
					$args['label'] = ucwords( $status );
				}
				$args['public'] = (boolean) $args['public'];
				if ( ! array_key_exists( 'exclude_from_search', $args ) ) {
					$args['exclude_from_search'] = ! $args['public'];
				}
				if ( ! array_key_exists( 'show_in_admin_all_list', $args ) ) {
					$args['show_in_admin_all_list'] = $args['public'];
				}
				if ( ! array_key_exists( 'show_in_admin_status_list', $args ) ) {
					$args['show_in_admin_status_list'] = (bool) $args['show_in_admin'];
				}
				if ( $args['show_in_admin_status_list'] ) {
					$string = sprintf( '%s <span class="count">(%%s)</span>', $args['label'] );
					$args['label_count'] = _n_noop( $string, $string );
				}
				unset( $args['show_in_admin'] );
				$stati[$status] = $output === 'array' ? $args : (object) $args;
			}
		}
		return $stati;
	} 

	/**
	 * @see https://github.com/WordPress/WordPress/blob/4.1-branch/wp-includes/post.php#L1079
	 */
	public static function get_stati( $args = [], $operator = 'and' ) {
		$stati = get_post_stati( [], 'object' );
		$self = new self();
		$_builtin = property_exists( $self, 'builtin' ) ? $self->builtin : [];
		$custom_stati = $self->custom_stati_args( 'object' );
		foreach ( $stati as $status => &$obj ) {
			if ( array_key_exists( $status, $_builtin ) ) {
				$obj->label = $_builtin[$status]['label'];
			}
		}
		$stati = array_merge( $stati, $custom_stati );
		return wp_filter_object_list( $stati, $args, $operator, false );
	}

}
