<?php
namespace WPDW\Device\Admin;

class attribute_meta_box extends post {

	const ATTR_BOX_ACTION_PREFIX = '_wpdw_admin_post_attribute_meta_box_';
	const ATTR_BOX_FORM_CLASS    = 'wpdw-admin-post-attr-box-form';

	/**
	 * @var array
	 */
	protected static $defaults = [
		'title'    => '',
		'context'  => ''
	];

	/**
	 * @var string
	 */
	private $post_type;

	/**
	 * @access public
	 * @var    string
	 */
	public $title;

	/**
	 * @var array
	 */
	private $attributes;

	/**
	 * Constructor
	 *
	 * @access public
	 *
	 * @uses   WPDW\_alias()
	 * @uses   WPDW\_property()
	 * @see    wp-domain-work/inc/functions.php
	 *
	 * @param  string $domain
	 * @return (void)
	 */
	public function __construct( $domain ) {
		if ( ! $domain = filter_var( $domain ) )
			return;
		if ( ! $this->post_type = \WPDW\_alias( $domain ) )
			return;
		parent::__construct( $domain );

		$this->init();
	}

	/**
	 * @access private
	 *
	 * @return (void)
	 */
	private function init() {
		$attrFilter = function( $attr ) {
			if ( in_array( $attr, self::$done_assets, true ) )
				return false;
			if ( get_post_type_object( $this->post_type )->hierarchical )
				return true;
			if ( self::$property && isset( self::$property->$attr ) )
				return true;
			return false;
		};
		if ( ! $this->attributes = array_filter( [ 'post_parent', 'menu_order' ], $attrFilter ) )
			return;

		add_action( 'add_meta_boxes_' . $this->post_type, [ &$this, 'add_meta_box' ] );
	}

	/**
	 * @access protected
	 *
	 * @see    WPDW\Device\Admin\post::prepare_arguments()
	 *
	 * @param  mixed  &$arg
	 * @param  string $key
	 * @return (void)
	 */
	protected function arguments_walker( &$arg, $key ) {
		if ( $key === 'context' ) :
			$arg = in_array( $arg, [ 'top', 'middle', 'bottom' ], true ) ? $arg : 'bottom';
		else :
			parent::arguments_walker( $arg, $key );
		endif;
	}

	/**
	 * @access public
	 *
	 * @return (void)
	 */
	public function add_meta_box() {
		if ( post_type_supports( $this->post_type, 'page-attributes' ) )
			remove_meta_box( 'pageparentdiv', $this->post_type, 'side' );

		$id = $this->post_type . 'attributediv';
		$title = $this->title ?: get_post_type_object( $this->post_type )->labels->name . __( 'Attributes' );
		$cb = [ &$this, 'attribute_meta_box' ];

		add_meta_box( $id, esc_html( $title ), [ &$this, 'attribute_meta_box' ], $this->post_type, 'side', 'core' );

		if ( ! $this->arguments )
			return;

		foreach ( $this->arguments as $args ) {
			/**
			 * @var string|array $asset
			 * @var string $id
			 * @var string $title
			 * @var string $context
			 * @var string $description Optional
			 */
			extract( $args );

			add_action( self::ATTR_BOX_ACTION_PREFIX . $context, [ &$this, '_render_' . $id ] );
			$args['_before_render']  = '<div class="' . self::ATTR_BOX_FORM_CLASS . '">';
			$args['_before_render'] .= '<p><strong>' . esc_html( $title ) . '</strong></p>';
			$args['_after_render']  = '</div>';

			self::$forms[$id] = $args;
		}
	}

	/**
	 * @access public
	 *
	 * @param  WP_Post $post
	 * @return (void)
	 */
	public function attribute_meta_box( \WP_Post $post ) {
		do_action( self::ATTR_BOX_ACTION_PREFIX . 'top', $post );

		if ( in_array( 'post_parent', $this->attributes, true ) )
			$this->post_parent_form( $post );

		do_action( self::ATTR_BOX_ACTION_PREFIX . 'middle', $post );

		if ( in_array( 'menu_order', $this->attributes, true ) )
			$this->menu_order_form( $post );

		do_action( self::ATTR_BOX_ACTION_PREFIX . 'bottom', $post );
	}

	/**
	 * @access private
	 *
	 * @see    https://github.com/mimosafa/WordPress/blob/4.1-branch/wp-admin/includes/meta-boxes.php#L710
	 *
	 * @todo   !!!!!
	 *
	 * @param  WP_Post $post
	 * @return (void)
	 */
	private function post_parent_form( \WP_Post $post ) {
		//
	}

	/**
	 * @access private
	 *
	 * @see    https://github.com/mimosafa/WordPress/blob/4.1-branch/wp-admin/includes/meta-boxes.php#L764
	 *
	 * @param  WP_Post $post
	 * @return (void)
	 */
	private function menu_order_form( \WP_Post $post ) {
		$label = 'Order';
		$attr  = '';
		if ( self::$property && isset( self::$property->menu_order ) ) {
			$setting = self::$property->get_setting( 'menu_order' );
			$label = $setting['label'];
			if ( $setting['readonly'] )
				$attr .= ' readonly="readonly"';
		}
?>
<div class="<?php esc_attr_e( self::ATTR_BOX_FORM_CLASS ); ?>">
<p><strong><?php _e( $label ) ?></strong></p>
<p>
	<label class="screen-reader-text" for="menu_order"><?php _e( $label ) ?></label>
	<input name="menu_order" type="number" id="menu_order" min="0" value="<?php echo esc_attr( $post->menu_order ) ?>" <?php echo $attr; ?>/>
</p>
</div>
<?php
	}

}
