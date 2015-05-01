<?php
namespace WPDW\Device;

trait status {
	use Module\Initializer, Module\Methods;
	use \WPDW\Util\Array_Function;

	private $status_labels = [];

	private static $def = [
		'label'  => \FILTER_SANITIZE_FULL_SPECIAL_CHARS,
		'labels' => [ 'filter' => \FILTER_SANITIZE_FULL_SPECIAL_CHARS, 'flags' => \FILTER_REQUIRE_ARRAY ],
		'public' => \FILTER_VALIDATE_BOOLEAN,
		'exclude_from_search'       => \FILTER_VALIDATE_BOOLEAN,
		'show_in_admin_all_list'    => \FILTER_VALIDATE_BOOLEAN,
		'show_in_admin_status_list' => \FILTER_VALIDATE_BOOLEAN,
	];
	private static $l_def = [
		'state'       => \FILTER_SANITIZE_FULL_SPECIAL_CHARS,
		'description' => \FILTER_SANITIZE_FULL_SPECIAL_CHARS,
		'action'      => \FILTER_SANITIZE_FULL_SPECIAL_CHARS,
	];
	private static $built_ins = [ 'publish', 'future', 'draft', 'pending', 'private', 'trash', 'auto-draft', 'inherit' ];

	/**
	 * @access protected
	 *
	 * @param  string $domain
	 * @return (void)
	 */
	protected function __construct() {
		if ( ! $this->isDefined( 'statuses' ) )
			return;
		array_walk( $this->statuses, [ $this, 'prepare_statuses' ] );
		if ( ! $this->statuses = array_filter( $this->statuses ) )
			return;

		$this->register_post_statuses();
	}

	private function prepare_statuses( &$arg, $status ) {
		if ( ! $arg || ! is_array( $arg ) )
			$arg = null;
		else if ( preg_match( '/[^a-z0-9_]/', $status ) )
			$arg = null;
		else if ( in_array( $status, self::$built_ins, true ) && ! $this->get_class_name( $status ) )
			$arg = null;

		if ( ! $arg )
			return;

		$arg = filter_var_array( $arg, self::$def );
		$arg['labels'] = filter_var_array( $arg['labels'] ?: [], self::$l_def );

		if ( ! $label = $arg['label'] ?: $arg['labels']['state'] ?: null ) {
			$arg = null;
			return;
		}
		if ( ! $arg['label'] )
			$arg['label'] = $label;

		$count_string = sprintf( '%s <span class="count">(%%s)</span>', $label );
		$arg['label_count'] = _n_noop( $count_string, $count_string );

		$this->status_labels[$status] = array_merge(
			[ 'state' => $label, 'description' => $label, 'action' => sprintf( __( 'Save as %s' ), $label ) ],
			array_filter( $arg['labels'] )
		);
		unset( $arg['labels'] );

		$arg = array_filter( $arg, function( $var ) { return isset( $var ); } );
	}

	/**
	 * @access private
	 * @param  string $status
	 * @return string|boolean
	 */
	private function get_class_name( $status ) {
		static $class_names = [];
		if ( ! $status = filter_var( $status ) )
			return false;
		if ( array_key_exists( $status, $class_names ) )
			return $class_names[$status];
		$class = __NAMESPACE__ . '\\status\\' . $status;
		if ( class_exists( $class ) ) {
			$class_names[$status] = $class;
			return $class;
		}
		return false;
	}

	private function register_post_statuses() {
		global $wp_post_statuses;
		foreach ( $this->statuses as $status => $args ) {
			if ( array_key_exists( $status, $wp_post_statuses ) ) {
				foreach ( $args as $key => $val )
					$wp_post_statuses[$status]->$key = $val;
			} else {
				register_post_status( $status, $args );
			}
		}
	}

}
