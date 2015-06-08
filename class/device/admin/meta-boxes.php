<?php
namespace WPDW\Device\Admin;

class meta_boxes extends post {

	/**
	 * Meta box id prefix
	 */
	private $box_id_prefix;

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
	 * @uses   WPDW\Device\Admin\post::__construct
	 *
	 * @param  string $domain
	 * @return (void)
	 */
	public function __construct( $domain ) {
		if ( ! $domain = filter_var( $domain ) )
			return;
		parent::__construct( $domain );
		$this->box_id_prefix = parent::BOX_ID_PREFIX . $domain . '-';
		self::$_defaults['callback'] = [ &$this, 'meta_box' ];
		add_action( 'add_meta_boxes', [ &$this, 'add_meta_boxes' ], 10, 2 );
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
		if ( ! $args = $this->prepare_arguments( $args ) )
			return;
		$this->meta_boxes[] = array_merge( self::$_defaults, $args );
	}

	/**
	 * Meta box arguments filter definition
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
				return in_array( $var, [ 'normal', 'advanced', 'side' ], true ) ? $var : 'advanced';
			};
			// priority
			$priorityVar = function( $var ) {
				return in_array( $var, [ 'high', 'core', 'default', 'low' ], true ) ? $var : 'default';
			};
			$def['context']  = [ 'filter' => \FILTER_CALLBACK, 'options' => $contextVar ];
			$def['priority'] = [ 'filter' => \FILTER_CALLBACK, 'options' => $priorityVar ];
		}
		return $def;
	}

	/**
	 * @access public
	 *
	 * @param  string  $post_type
	 * @param  WP_Post $post
	 */
	public function add_meta_boxes( $post_type, \WP_Post $post ) {
		if ( $this->meta_boxes ) {
			foreach ( $this->meta_boxes as $args ) {
				/**
				 * @var string $id
				 * @var string $title
				 * @var array|string $asset
				 * @var string $description
				 */
				extract( $args );

				$context  = ! is_array( $context ) ? $context : array_shift( WPDW\Util\Array_Function::flatten( $context, true ) );
				$priority = ! is_array( $priority ) ? $priority : array_shift( WPDW\Util\Array_Function::flatten( $priority, true ) );
				$callback_args = [];

				if ( is_array( $asset ) ) :

					foreach ( $asset as $a ) {
						self::$asset_values[$a] = $this->property->$a->get( $post );
					}
					$callback = [ &$this, 'plural_assets_form_table' ];
					$callback_args['assets'] = $asset;

				else :

					self::$asset_values[$asset] = $this->property->$asset->get( $post );
					$callback = [ &$this, 'print_fieldset_' . $asset ];

				endif;

				add_meta_box( $this->box_id_prefix . $id, $title, $callback, $post_type, $context, $priority, $callback_args );
			}
		}
	}

	public function __call( $func, $a ) {
		if ( preg_match( '/\Aprint_fieldset_([a-z][a-z0-9_]+)/', $func, $m ) ) :
			$asset = $m[1];
			printf( '<fieldset id="%s"></fieldset>', esc_attr( self::FORM_ID_PREFIX . $asset ) );
		endif;
	}

	/**
	 * @access public
	 */
	public function plural_assets_form_table( $post, $metabox ) {
		if ( ! $assets = $metabox['args']['assets'] )
			return;
		echo "<table class=\"form-table\">\n\t<tbody>\n";
		foreach ( (array) $assets as $asset ) {
			$label = $this->property->get_setting( $asset )['label'];
			printf(
				"\t\t<tr>\n\t\t\t<th><label for=\"%2\$s\">%1\$s</label></th>\n\t\t\t<td><fieldset id=\"%2\$s\"></fieldset></td>\n",
				esc_html( $label ),
				esc_attr( self::FORM_ID_PREFIX . $asset )
			);
		}
		echo "\t</tbody>\n</table>";
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
		$asset = $metabox['args']['asset'];
		$args = $this->get_recipe( $asset, $metabox['args'], $post );
		self::$template->output( $args );
	}

}
