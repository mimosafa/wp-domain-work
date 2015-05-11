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
	 * @access protected
	 *
	 * @param  string $domain
	 * @return (void)
	 */
	protected function __construct() {
		if ( ! $this->isDefined( 'statuses' ) )
			return;
		array_walk( $this->statuses, [ $this, 'prepare_statuses' ] );
		$this->statuses = array_filter( $this->statuses );
	}

	/**
	 * Initialize domain's(post_type's) post status
	 * 
	 * @access public
	 * 
	 * @see    WPDW\Device\admin::init()
	 * @return (void)
	 */
	public function init() {
		if ( ! $this->isDefined( 'statuses' ) )
			return;

		global $wp_post_statuses;
		foreach ( $this->statuses as $status => $args ) {
			if ( array_key_exists( $status, $wp_post_statuses ) ) {
				foreach ( $args as $key => $val )
					$wp_post_statuses[$status]->$key = $val;
			} else {
				register_post_status( $status, $args );
			}
		}

		/**
		 * Check current global post_type
		 */
		if ( explode( '\\', __CLASS__ )[1] !== \WPDW\_domain( $this->get_post_type() ) )
			return;

		if ( $this->status_labels ) {
			foreach ( $this->status_labels as $status => $labels ) {
				if ( $class = $this->get_class_name( $status ) ) {
					new $class( $labels );
				} else {
					Status\custom::add( $status, $labels );
				}
			}
		}
	}

	public static function get_post_stati( $args = [], $output = 'names', $operator = 'and' ) {
		//
	}

	/**
	 * @access private
	 * 
	 * @param  array  &$arg
	 * @param  string $status
	 * @return (void)
	 */
	private function prepare_statuses( &$arg, $status ) {
		// WordPress built-in statuses
		static $built_ins = [
			'publish', 'future', 'draft', 'pending',
			'private', 'trash', 'auto-draft', 'inherit'
		];

		if ( ! $arg || ! is_array( $arg ) ) :
			$arg = null;
		elseif ( preg_match( '/[^a-z0-9_]/', $status ) ) :
			$arg = null;
		elseif ( in_array( $status, $built_ins, true ) && ! $this->get_class_name( $status ) ) :
			$arg = null;
		else :
			if ( $class = $this->get_class_name( $status ) ) {
				$arg = filter_var_array( $arg, $this->get_filter_definition( 'built-in' ) );
				$labels_def = $class::get_filter_definition();
			} else {
				$arg = filter_var_array( $arg, $this->get_filter_definition( 'custom' ) );
				$labels_def = Status\custom::get_filter_definition();
			}
			$arg['labels'] = filter_var_array( $arg['labels'] ?: [], $labels_def, false );
			if ( ! $label = $arg['label'] ?: $arg['labels']['name'] ?: null )
				$arg = null;
		endif;

		if ( ! $arg )
			return;

		if ( ! $arg['label'] )
			$arg['label'] = $label;

		$count_string = sprintf( '%s <span class="count">(%%s)</span>', $label );
		$arg['label_count'] = _n_noop( $count_string, $count_string );

		$action = array_key_exists( 'action', $arg['labels'] ) ? $arg['labels']['action'] : null;
		$defaults = $class
			? $class::get_defaults( $label, $action )
			: Status\custom::get_defaults( $label )
		;

		$this->status_labels[$status] = array_merge( $defaults, $arg['labels'] );
		unset( $arg['labels'] );

		$arg = array_filter( $arg, function( $var ) { return isset( $var ); } );
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
			if ( $post_type = filter_input( \INPUT_GET, 'post_type' ) )
				return $post_type;
			else if ( $post = filter_input( \INPUT_GET, 'post', \FILTER_VALIDATE_INT ) )
				return \get_post_type( $post );
			else if ( $post_type = filter_input( \INPUT_POST, 'post_type' ) )
				return $post_type;
			else
				return '';
		}
	}

	/**
	 * @access private
	 *
	 * @uses   WPDW\Device\Status\{$status}
	 * 
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
			static $built_in = [
				'label'  => \FILTER_SANITIZE_FULL_SPECIAL_CHARS,
				'labels' => [ 'filter' => \FILTER_SANITIZE_FULL_SPECIAL_CHARS, 'flags' => \FILTER_REQUIRE_ARRAY ]
			];
			return $built_in;
		} else if ( 'custom' === $context ) {
			static $custom;
			if ( ! $custom ) {
				$custom = array_merge(
					$this->get_filter_definition( 'built-in' ),
					[
						'public' => \FILTER_VALIDATE_BOOLEAN,
						'exclude_from_search'       => \FILTER_VALIDATE_BOOLEAN,
						'show_in_admin_all_list'    => \FILTER_VALIDATE_BOOLEAN,
						'show_in_admin_status_list' => \FILTER_VALIDATE_BOOLEAN,
					]
				);
			}
			return $custom;
		}
	}

}
