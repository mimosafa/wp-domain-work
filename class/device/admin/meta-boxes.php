<?php
namespace WPDW\Device\Admin;

class meta_boxes extends post {

	/**
	 * Meta box id prefix
	 */
	const BOX_ID_PREFIX  = 'wpdw-meta-box-';

	/**
	 * @var array
	 */
	private $meta_boxes = [];

	/**
	 * @var array
	 */
	private static $defaults = [
		'title' => '',
		'context'  => '',
		'priority' => '',
	];

	/**
	 * Constructor
	 *
	 * @access public
	 *
	 * @uses   WPDW\Device\Admin\post::__construct
	 *
	 * @param  string $domain
	 * @return (void)
	 */
	public function __construct( $domain ) {
		if ( ! $domain = filter_var( $domain ) )
			return;

		parent::__construct( $domain );
		add_action( 'add_meta_boxes', [ &$this, 'add_meta_boxes' ], 10, 2 );
	}

	/**
	 * @access public
	 *
	 * @param  array $args
	 * @return (void)
	 */
	public function add( Array $args ) {
		$args = array_merge( self::$defaults, $args );
		$this->prepare_arguments( $args );
		if ( ! isset( $args['asset'] ) || ! $args['asset'] )
			return;
		$this->meta_boxes[] = $args;
	}

	/**
	 * @access protected
	 *
	 * @param  mixed  &$arg
	 * @param  string $key
	 * @return (void)
	 */
	protected function arguments_walker( &$arg, $key ) {
		if ( $key === 'context' ) :
			$arg = in_array( $arg, [ 'normal', 'advanced', 'side' ], true ) ? $arg : 'advanced';
		elseif ( $key === 'priority' ) :
			$arg = in_array( $arg, [ 'high', 'core', 'default', 'low' ], true ) ? $arg : 'default';
		else :
			parent::arguments_walker( $arg, $key );
		endif;
	}

	/**
	 * @access public
	 *
	 * @param  string  $post_type
	 * @param  WP_Post $post
	 */
	public function add_meta_boxes( $post_type, \WP_Post $post ) {
		if ( ! $this->meta_boxes )
			return;

		foreach ( $this->meta_boxes as $args ) {
			/**
			 * @var string|array $asset
			 * @var string $id
			 * @var string $title
			 * @var string $context
			 * @var string $priority
			 * @var string $description Optional
			 */
			extract( $args );

			$callback = [ &$this, '_render_' . $id ];
			add_meta_box( self::BOX_ID_PREFIX . $id, $title, $callback, $post_type, $context, $priority );

			self::$forms[$id] = $args;
		}

	}

}
