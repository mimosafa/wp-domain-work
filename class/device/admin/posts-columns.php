<?php
namespace WPDW\Device\Admin;

class posts_columns {

	/**
	 * @var string
	 */
	private $post_type;

	/**
	 * @var array
	 */
	private $columns = [];
	private $sortable_columns = [];
	private $narrow_columns = [];

	/**
	 * @var WP_Domain\{$domain}\property
	 */
	private $property;

	/**
	 * Default columns
	 */
	private static $built_ins = [ 'cb', 'title', 'author ', 'categories ', 'tags', 'comments ', 'date' ];

	/**
	 * Constructor
	 *
	 * @access public
	 *
	 * @param  string $domain
	 * @return (void)
	 */
	public function __construct( $domain ) {
		if ( ! $domain = filter_var( $domain ) )
			return;
		if ( ! $this->post_type = \WPDW\_alias( $domain ) )
			return;
		// property instance
		$this->property = \WPDW\_property_object( $domain );
		$this->init();
	}

	/**
	 * @access public
	 *
	 * @param  string $name
	 * @param  mixed  $args
	 */
	public function add( $name, $args ) {
		if ( ! $args || ! $name = filter_var( $name ) )
			return;
		if ( ! in_array( $name, self::$built_ins, true ) && ! isset( $this->property->$name ) )
			return;
		$this->columns[$name] = '';
		
		$args = is_array( $args ) ? $args : [];
		static $definition = [
			'label' => \FILTER_SANITIZE_FULL_SPECIAL_CHARS,
			'sortable' => \FILTER_VALIDATE_BOOLEAN,
			'narrow' => \FILTER_VALIDATE_BOOLEAN,
		];
		$args = filter_var_array( $args, $definition );
		if ( isset( $args['sortable'] ) )
			$this->sortable_columns[$name] = [ $name, $args['sortable'] ];
		if ( $args['label'] ) 
			$label = $args['label'];
		else if ( $setting = $this->property->get_setting( $name ) )
			$label = $setting['label'];
		if ( isset( $label ) ) {
			$this->columns[$name] .= $label;
			if ( $args['narrow'] )
				$this->narrow_columns[$name] = mb_strlen( $label ) + ( isset( $args['sortable'] ) ? 3 : 1 );
		}
	}

	/**
	 * @access private
	 */
	private function init() {
		add_filter( 'manage_' . $this->post_type . '_posts_columns', [ $this, 'manage_posts_columns' ] );
		add_filter( 'manage_edit-' . $this->post_type . '_sortable_columns', [ $this, 'manage_sortable_columns' ] );
		add_action( 'manage_' . $this->post_type . '_posts_custom_column', [ $this, 'column_callback' ], 10, 2 );
		add_action( 'admin_print_styles', [ $this, 'columns_style' ], 99 );
	}

	/**
	 * @access public
	 *
	 * @param  array $columns
	 * @return array
	 */
	public function manage_posts_columns( Array $columns ) {
		if ( $this->columns ) {
			$new_columns = [];
			foreach ( $this->columns as $name => $label ) {
				if ( ! $label && isset( $columns[$name] ) )
					$label = $columns[$name];
				if ( $label )
					$new_columns[$name] = $label;
			}
			if ( ! empty( $new_columns ) )
				$columns = array_merge( [ 'cb' => $columns['cb'] ], $new_columns );
		}
		return $columns;
	}

	/**
	 * @access public
	 *
	 * @param  array $sortable_columns
	 * @return array
	 */
	public function manage_sortable_columns( $sortable_columns ) {
		if ( $this->sortable_columns ) {
			$sortable_columns = array_merge( $this->sortable_columns, $sortable_columns );
		}
		return $sortable_columns;
	}

	/**
	 * @access public
	 *
	 * @uses WPDW\Device\Asset\type_{$type}
	 *
	 * @param  string $column_name
	 * @param  int    $post_id
	 * @return (void)
	 */
	public function column_callback( $column_name, $post_id ) {
		static $done = [];
		$data =& $this->property->$column_name;
		if ( ! in_array( $column_name, $done, true ) ) {
			/**
			 * Add filter for printing in column only once
			 *
			 * @uses WPDW\Device\Asset\type_{$type}::print_column()
			 */
			add_filter( '_wpdw_' . $column_name . '_column', [ $data, 'print_column' ], 10, 2 );
			$done[] = $column_name;
		}
		$value = $data->get( $post_id );
		echo apply_filters( '_wpdw_' . $column_name . '_column', $value, $post_id );
	}

	/**
	 * @access public
	 */
	public function columns_style() {
		if ( ! $this->columns )
			return;

		$styles = '';
		$callback = function( $var ) { return ! in_array( $var, self::$built_ins ); };
		if ( $customs = array_filter( array_keys( $this->columns ), $callback ) ) {
			array_walk( $customs, function( &$class ) { $class = '.column-' . $class; } );
			$styles .= sprintf( "\t@media screen and (max-width: 782px) { %s { display: none; } }\n", implode( ', ', $customs ) );
		}
		if ( $this->narrow_columns ) {
			foreach ( $this->narrow_columns as $name => $em )
				$styles .= sprintf( "\tth.column-%s { width: %dem; }\n", $name, $em );
		}
		if ( ! $styles )
			return;

		echo <<<EOF
<style type="text/css">
{$styles}</style>\n
EOF;
	}

}
