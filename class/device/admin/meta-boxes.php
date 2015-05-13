<?php
namespace WPDW\Device\Admin;

class meta_boxes extends post {

	/**
	 * Meta box id prefix
	 */
	const BOX_ID_PREFIX = 'wp-domain-work-meta-box-';

	/**
	 * @var array
	 */
	private $meta_boxes = [];

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
	 * @access public
	 *
	 * @uses   WPDW\_property_object()
	 * @see    wp-domain-work/inc/functions.php
	 *
	 * @param  string $domain
	 */
	public function __construct( $domain ) {
		parent::__construct( $domain );
		self::$_defaults['callback'] = [ &$this, 'meta_box' ];
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
		if ( ! $args = $this->prepare_arguments( 'meta_box', $args ) )
			return;
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
	public function meta_box( $post, $metabox ) {
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
