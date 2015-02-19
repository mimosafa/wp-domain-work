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

	private function init() {
		if ( property_exists( $this, 'builtin' ) && is_array( $this->builtin ) && $this->builtin ) {
			$this->init_builtin_statuses();
		}
		if ( property_exists( $this, 'custom' ) && is_array( $this->custom ) && $this->custom ) {
			$this->init_custom_statuses();
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
			\WP_Domain_Work\WP\post\post_status\custom_post_status::set( $status, $args );
		}
	}

}
