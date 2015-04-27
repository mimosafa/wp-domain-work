<?php
namespace WPDW\Device\Admin;

class meta_boxes {

	/**
	 * Meta box id prefix
	 */
	const BOX_ID_PREFIX = 'wp-domain-work-meta-box-';

	/**
	 * @var array
	 */
	private $meta_boxes = [];

	/**
	 * @var  WP_Domain\{$domain}\property
	 */
	private $property;

	/**
	 * @var WPDW\Device\Admin\template
	 */
	private $template;

	// private $save_post = [];

	/**
	 * Default arguments, also function as array sorter.
	 * @var array
	 */
	private static $_defaults = [
		'id'       => null,
		'title'    => null,
		'callback' => null,
		'screen'   => null,
		'context'  => 'advanced',
		'priority' => 'default'
	];

	/**
	 * Constructor
	 *
	 * @access protected
	 * @param  string $domain
	 */
	public function __construct( $domain ) {
		if ( ! $domain = filter_var( $domain ) )
			return;
		// property instance
		$propClass = 'WP_Domain\\' . $domain . '\\property';
		if ( class_exists( $propClass ) )
			$this->property = $propClass::getInstance();
		// template instance
		$this->template = new template( $domain );

		self::$_defaults['callback'] = [ &$this, 'print_html' ];
		add_action( 'add_meta_boxes', [ &$this, 'add_meta_boxes' ] );
	}

	/**
	 * @access public
	 * @param  array $args {
	 *     @see WPDW\Device\admin::get_filter_definition()
	 *
	 *     @type string        $id  If $asset key exists, Optional
	 *     @type string        $title
	 *     @type null|callable $callback
	 *     @type string        $screen
	 *     @type string        $context
	 *     @type string        $priority
	 *     @type string|array  $asset  If $callback is callable, Optional
	 *     @type string        $description  Optional
	 * }
	 * @return (void)
	 */
	public function add( Array $args ) {
		$args = array_merge( self::$_defaults, $args );
		if ( ! $args['id'] )
			$args['id'] = implode( '-', (array) $args['asset'] );
		if ( ! $args['title'] )
			$args['title'] = ucwords( str_replace( [ '-', '_' ], [ ' / ', ' ' ], $args['id'] ) );
		$callback_args = array_splice( $args, 6 );
		extract( $args );
		$this->meta_boxes[] = [ self::BOX_ID_PREFIX . $id, $title, $callback, $screen, $context, $priority, $callback_args ];
	}

	/**
	 * @access public
	 */
	public function add_meta_boxes() {
		if ( $this->meta_boxes ) {
			foreach ( $this->meta_boxes as $args ) {
				/**
				 * @var array $args {
				 *     @see  http://codex.wordpress.org/Function_Reference/add_meta_box
				 *
				 *     @type string   $id
				 *     @type string   $title
				 *     @type callable $callback
				 *     @type string   $screen
				 *     @type string   $context
				 *     @type string   $priority
				 *     @type array    $callback_args {
				 *
				 *         @type string|array $asset Callable string ( OR strings array )
				 *                                   by WP_Domain\{$domain}\property::get()
				 *
				 *         @type string       $description Optional.
				 *
				 *         ...and so on
				 *     }
				 * }
				 */
				call_user_func_array( 'add_meta_box', $args );
			}
		}
	}

	/**
	 * Print meta box
	 * 
	 * @param  WP_Post $post
	 * @param  array $metabox {
	 *     @type string       $domain
	 *     @type string|array $asset
	 *     @type string       $description (Optional)
	 * }
	 * @return (void)
	 */
	public function print_html( $post, $metabox ) {
		if ( ! $this->property )
			return;

		// asset
		$asset  = $metabox['args']['asset'];

		if ( is_array( $asset ) ) {
			$args = [ 'type' => 'group', 'assets' => [] ];
			foreach ( $asset as $a )
				$args['assets'][] = $this->property->$a->get_vars( $post );
		} else {
			$args = $this->property->$asset->get_vars( $post );
		}

		// description
		if ( array_key_exists( 'description', $metabox['args'] ) )
			$args = array_merge( $args, [ 'description' => $metabox['args']['description'] ] );

		$this->template->output( $args );
	}

}
