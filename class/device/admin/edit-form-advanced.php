<?php
namespace WPDW\Device\Admin;

class edit_form_advanced extends post {

	const DIV_ID_PREFIX  = 'wpdw-admin-post-div-';

	/**
	 * @var array
	 */
	protected static $defaults = [
		'context'  => ''
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
	 * @access protected
	 *
	 * @param  mixed  &$arg
	 * @param  string $key
	 * @return (void)
	 */
	protected function arguments_walker( &$arg, $key ) {
		if ( $key === 'context' ) :
			/**
			 * You know, these below texts added prefix 'edit_form_' will be action_hook in edit-form-advanced.php
			 * @var string $context
			 */
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
		if ( ! $this->arguments )
			return;

		foreach ( $this->arguments as $args ) {
			/**
			 * @var string|array $asset
			 * @var string $id
			 * @var string $context
			 * @var string $title       Optional
			 * @var string $description Optional
			 */
			extract( $args );

			add_action( 'edit_form_' . $context, [ &$this, '_render_' . $id ] );

			$args['_before_render'] = '<div class="inside" id="' . self::DIV_ID_PREFIX . esc_attr( $id ) . '">';
			if ( isset( $title ) )
				$args['_before_render'] .= '<h3>' . esc_html( $title ) . '</h3>';
			$args['_after_render']  = '</div>';

			self::$forms[$id] = $args;
		}
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
