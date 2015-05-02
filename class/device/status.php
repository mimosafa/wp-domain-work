<?php
namespace WPDW\Device;

trait status {
	use \WPDW\Util\Singleton, Module\Methods;
	use \WPDW\Util\Array_Function;

	/**
	 * @var array
	 */
	private $status_labels = [];

	/**
	 * @var array
	 */
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

		if ( explode( '\\', __CLASS__ )[1] === \WPDW\_domain( $this->get_post_type() ) )
			$this->init();
	}

	/**
	 * @access public
	 * @return array
	 */
	public function get_labels() {
		return $this->status_labels;
	}

	/**
	 * @access public
	 * 
	 * @param  string $status
	 * @return string|boolean
	 */
	public function get_class_name( $status ) {
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

	/**
	 * @access private
	 * 
	 * @return (void)
	 */
	private function init() {
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

	/**
	 * @access private
	 * 
	 * @param  array  &$arg
	 * @param  string $status
	 * @return (void)
	 */
	private function prepare_statuses( &$arg, $status ) {
		if ( ! $arg || ! is_array( $arg ) ) :
			$arg = null;
		elseif ( preg_match( '/[^a-z0-9_]/', $status ) ) :
			$arg = null;
		elseif ( in_array( $status, self::$built_ins, true ) && ! $this->get_class_name( $status ) ) :
			$arg = null;
		endif;

		if ( ! $arg )
			return;

		if ( $this->get_class_name( $status ) )
			$arg = filter_var_array( $arg, $this->get_filter_definition( 'built-in' ) );
		else
			$arg = filter_var_array( $arg, $this->get_filter_definition( 'custom' ) );
		$arg['labels'] = filter_var_array( $arg['labels'] ?: [], $this->get_filter_definition( 'labels' ), false );

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
			$arg['labels']
		);
		unset( $arg['labels'] );

		$arg = array_filter( $arg, function( $var ) { return isset( $var ); } );
	}

	/**
	 * Get filter definition
	 *
	 * @access private
	 * 
	 * @param  string $context
	 * @return array
	 */
	private function get_filter_definition( $context ) {
		if ( 'built-in' === $context ) {
			static $built_in_def = [
				'label'  => \FILTER_SANITIZE_FULL_SPECIAL_CHARS,
				'labels' => [ 'filter' => \FILTER_SANITIZE_FULL_SPECIAL_CHARS, 'flags' => \FILTER_REQUIRE_ARRAY ]
			];
			return $built_in_def;
		} else if ( 'custom' === $context ) {
			static $custom_def;
			if ( ! $custom_def ) {
				$custom_def = array_merge(
					$this->get_filter_definition( 'built-in' ),
					[
						'public' => \FILTER_VALIDATE_BOOLEAN,
						'exclude_from_search'       => \FILTER_VALIDATE_BOOLEAN,
						'show_in_admin_all_list'    => \FILTER_VALIDATE_BOOLEAN,
						'show_in_admin_status_list' => \FILTER_VALIDATE_BOOLEAN,
					]
				);
			}
			return $custom_def;
		} else if ( 'labels' === $context ) {
			static $labels_def = [
				'state'       => \FILTER_SANITIZE_FULL_SPECIAL_CHARS,
				'description' => \FILTER_SANITIZE_FULL_SPECIAL_CHARS,
				'action'      => \FILTER_SANITIZE_FULL_SPECIAL_CHARS,
			];
			return $labels_def;
		}
	}

	/**
	 * Get current screen post_type
	 *
	 * @access private
	 * @return string
	 */
	private function get_post_type() {
		if ( ! is_admin() ) {
			global $wp_query;
			return $wp_query->get( 'post_type' );
		} else {
			return
				filter_input( \INPUT_GET, 'post_type' ) ?:
				\get_post_type( filter_input( \INPUT_GET, 'post' ) ) ?:
				filter_input( \INPUT_POST, 'post_type' ) ?:
				''
			;
		}
	}

}
