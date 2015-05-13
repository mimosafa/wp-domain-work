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
	 * @uses   WPDW\_property_object()
	 * @see    wp-domain-work/inc/functions.php
	 *
	 * @param  string $domain
	 */
	public function __construct( $domain ) {
		parent::__construct( $domain );
		add_action( 'dbx_post_advanced', [ &$this, 'add_edit_forms' ] );
	}

	/**
	 * @access public
	 */
	public function add( Array $args ) {
		if ( ! $args = $this->prepare_arguments( 'edit_form', $args ) )
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
	 * @access public
	 */
	public function add_edit_forms() {
		if ( $this->edit_forms = array_filter( $this->edit_forms ) ) {
			foreach ( array_keys( $this->edit_forms ) as $hook )
				add_action( $hook, [ &$this, 'edit_forms' ] );
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
		// asset
		$asset = $array['args']['asset'];

		if ( is_array( $asset ) ) {
			$args = [ 'type' => 'group', 'assets' => [] ];
			foreach ( $asset as $a )
				$args['assets'][] = $this->property->$a->get_vars( $post );
		} else {
			$args = $this->property->$asset->get_vars( $post );
		}

		// description
		if ( array_key_exists( 'description', $array['args'] ) )
			$args = array_merge( $args, [ 'description' => $array['args']['description'] ] );

		echo "\t<h3>{$array['title']}</h3>";
		$this->template->output( $args );
	}

}
