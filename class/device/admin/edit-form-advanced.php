<?php
namespace WPDW\Device\Admin;

class edit_form_advanced extends post {

	/**
	 * ID formats
	 */
	const DIV_ID_PREFIX = 'wpdw-div-';

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
	private static $_defaults = [
		'id'       => null,
		'title'    => null,
		'callback' => null,
		'context'  => 'after_editor',
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
		parent::__construct( $domain );
		add_action( 'dbx_post_advanced', [ &$this, 'add_edit_forms' ] );
	}

	/**
	 * @access public
	 */
	public function add( Array $args ) {
		if ( ! $args = $this->prepare_arguments( $args ) )
			return;
		$args = array_merge( self::$_defaults, $args );
		if ( ! $args['id'] )
			$args['id'] = implode( '-', (array) $args['asset'] );
		if ( ! $args['title'] )
			$args['title'] = ucwords( str_replace( [ '-', '_' ], [ ' / ', ' ' ], $args['id'] ) );
		$callback_args = array_splice( $args, 4 );
		extract( $args );
		$hook = 'edit_form_' . $args['context'];
		$this->edit_forms[$hook][] = [ 'id' => $id, 'title' => $title, 'callback' => $callback, 'args' => $callback_args ];
	}

	/**
	 * Edit form arguments filter definition
	 *
	 * @access protected
	 *
	 * @uses   WPDW\Device\Admin\post::get_filter_definition()
	 *
	 * @return array
	 */
	protected function get_filter_definition() {
		static $def;
		if ( ! $def ) {
			$def = parent::get_filter_definition();
			// context
			$contextVar = function( $var ) {
				return in_array( $var, [ 'top', 'before_permalink', 'after_title', 'after_editor' ], true ) ? $var : null;
			};
			$def['context'] = [ 'filter' => \FILTER_CALLBACK, 'options' => $contextVar ];
		}
		return $def;
	}

	/**
	 * @access public
	 */
	public function add_edit_forms() {
		if ( $this->edit_forms = array_filter( $this->edit_forms ) ) {
			foreach ( array_keys( $this->edit_forms ) as $hook )
				add_action( $hook, [ &$this, 'edit_forms' ] );
			\WPDW\Scripts::add_data( 'editforms', 1 );
		}
	}

	/**
	 * @access public
	 */
	public function edit_forms( $post ) {
		foreach ( array_keys( $this->edit_forms ) as $hook ) {
			if ( doing_action( $hook ) )
				break;
		}
		$args = $this->edit_forms[$hook];
		unset( $this->edit_forms[$hook] );

		foreach ( $args as $array ) {
			$divid = self::DIV_ID_PREFIX . $array['id'];
			echo "<div id=\"{$divid}\">\n";
			if ( is_callable( $array['callback'] ) ) {
				$cb = $array['callback'];
				unset( $array['id'] );
				unset( $array['callback'] );
				$array['post'] = $post;
				echo "\t<h3>{$array['title']}</h3>";
				call_user_func_array( $cb, $array );
			} else {
				$this->print_edit_form( $array, $post );
			}
			echo '</div>';
		}
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

}
