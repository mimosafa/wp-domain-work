<?php
namespace WPDW\Device\Admin;

class meta_boxes extends post {

	/**
	 * Meta box id prefix
	 */
	const BOX_ID_PREFIX  = 'wpdw-admin-post-meta-box-';

	/**
	 * @var array
	 */
	protected static $defaults = [
		'title'    => '',
		'context'  => '',
		'priority' => ''
	];

	/**
	 * @access protected
	 */
	protected function init() {
		add_action( 'add_meta_boxes', [ &$this, 'add_meta_boxes' ], 10, 2 );
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
		if ( ! $this->arguments )
			return;

		foreach ( $this->arguments as $args ) {
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
