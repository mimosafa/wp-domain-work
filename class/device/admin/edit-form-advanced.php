<?php
namespace WPDW\Device\Admin;

class edit_form_advanced extends post {

	const DIV_ID_PREFIX  = 'wpdw-div-';

	/**
	 * @var array
	 */
	private $edit_forms = [
		'edit_form_top' => [],
		'edit_form_before_permalink' => [],
		'edit_form_after_title'  => [],
		'edit_form_after_editor' => [],
	];

	/**
	 * Default arguments
	 * @var array
	 */
	private static $defaults = [
		'title'    => '',
		'context'  => '',
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
		add_action( 'dbx_post_advanced', [ &$this, 'add_edit_forms' ] ); // @todo 'dbx_post_advanced' has been deprecated.
	}

	/**
	 * @access public
	 */
	public function add( Array $args ) {
		$args = array_merge( self::$defaults, $args );
		$this->prepare_arguments( $args );
		if ( ! isset( $args['asset'] ) || ! $args['asset'] )
			return;

		$hook = 'edit_form_' . $args['context'];
		$this->edit_forms[$hook][] = $args;
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
			$arg = in_array( $arg, [ 'top', 'before_permalink', 'after_title', 'after_editor' ], true ) ? $arg : 'after_editor';
		elseif ( $key === 'list_table' ) :
			$arg = filter_var( $arg, \FILTER_VALIDATE_BOOLEAN );
		else :
			parent::arguments_walker( $arg, $key );
		endif;
	}

	/**
	 * @access public
	 */
	public function add_edit_forms( \WP_Post $post ) {
		if ( ! $this->edit_forms = array_filter( $this->edit_forms ) )
			return;

		foreach ( $this->edit_forms as $hook => $forms ) {
			foreach ( $forms as $args ) {
				/**
				 * @var string|array $asset
				 * @var string $id
				 * @var string $title
				 * @var string $context
				 * @var string $description Optional
				 */
				extract( $args );

				$callback = [ &$this, 'render_' . $id ];
				add_action( $hook, $callback );

				self::$forms[$id] = $args;
			}
		}
	}

	/**
	 * Render form element
	 *
	 * @access protected
	 *
	 * @uses   WPDW\Device\Admin\Post::output_asset_form()
	 *
	 * @param  string  $asset
	 * @param  WP_Post $post
	 * @return (void)
	 */
	protected function output_asset_form( $asset, \WP_Post $post ) {
		echo '<div class="inside" id="' . self::DIV_ID_PREFIX . esc_attr( $asset ) . '">';
		parent::output_asset_form( $asset, $post );
		echo '</div>';
	}

	/**
	 * @access private
	 *
	 * @param  array $array
	 * @param  WP_Post $post
	 * @return (void)
	 */
	private function print_edit_form( $array, \WP_Post $post ) {
		$asset = $array['args']['asset'];
		$args  = $this->get_recipe( $asset, $array['args'], $post );
		echo "\t<h3>{$array['title']}</h3>";
		self::$template->output( $args );
	}

	/**
	 * @access private
	 *
	 * @param  array $array
	 * @param  WP_Post $post
	 * @return (void)
	 */
	private function print_list_table( Array $array, \WP_Post $post ) {
		$asset = $this->property->$array['args']['asset']->get_recipe( $post );

		$args = array_merge( $array, $asset );

		#_var_dump( $args );

		$table = new WPDW_List_Table( $args );
		$table->prepare_items();
		if ( isset( $array['display_title'] ) && $array['display_title'] )
			echo "\t<h3>{$array['title']}</h3>";
		$table->display();
	}

}
