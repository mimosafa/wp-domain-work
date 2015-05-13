<?php
namespace WPDW\Device\Admin;

class attribute_meta_box extends post {

	/**
	 * Action tag prefix
	 */
	const ACTION_PREFIX = '_wpdw_admin_attribute_meta_box_';

	/**
	 * @var string
	 */
	private $post_type;
	private $title;

	/**
	 * @var WP_Domain\{$domain}\property
	 */
	protected $property;

	/**
	 * @var array
	 */
	private $attributes;

	/**
	 * @var array
	 */
	private static $def = [
		'title' => \FILTER_SANITIZE_FULL_SPECIAL_CHARS,
		'attributes' => [ 'filter' => \FILTER_SANITIZE_FULL_SPECIAL_CHARS, 'flags' => \FILTER_FORCE_ARRAY ],
	];

	/**
	 * Constructor
	 *
	 * @access public
	 *
	 * @uses   WPDW\_alias()
	 * @uses   WPDW\_property_object()
	 * @see    wp-domain-work/inc/functions.php
	 *
	 * @param  string $domain
	 * @param  array  $args
	 * @return (void)
	 */
	public function __construct( $domain, Array $args ) {
		if ( ! $this->post_type = \WPDW\_alias( $domain ) )
			return;
		$this->property = \WPDW\_property_object( $domain );
		$args = filter_var_array( $args, self::$def );
		if ( $this->attributes = array_filter( $args['attributes'], [ $this, 'attributes_filter' ] ) ) {
			$this->title = $args['title'] ?: get_post_type_object( $this->post_type )->labels->name . __( 'Attributes' );
			$this->init();
		}
	}

	/**
	 * @access private
	 *
	 * @param  string $attr
	 * @return boolean
	 */
	private function attributes_filter( $attr ) {
		/**
		 * @uses WPDW\Device\Admin\post::$done_assets
		 */
		if ( in_array( $attr, self::$done_assets, true ) )
			return false;
		if ( $this->property && isset( $this->property->$attr ) )
			return true;
		else if ( get_post_type_object( $this->post_type )->hierarchical )
			return true;
		return false;
	}

	/**
	 * @access private
	 *
	 * @return (void)
	 */
	private function init() {
		add_action( 'add_meta_boxes_' . $this->post_type, [ $this, 'add_meta_box' ] );
	}

	/**
	 * @access public
	 *
	 * @return (void)
	 */
	public function add_meta_box() {
		if ( post_type_supports( $this->post_type, 'page-attributes' ) )
			remove_meta_box( 'pageparentdiv', $this->post_type, 'side' );
		add_meta_box( $this->post_type . 'attributediv', $this->title, [ $this, 'attribute_meta_box' ], $this->post_type, 'side', 'core' );
	}

	/**
	 * @access public
	 *
	 * @param  WP_Post $post
	 * @return (void)
	 */
	public function attribute_meta_box( $post ) {
		do_action( self::ACTION_PREFIX . 'top', $post );

		if ( in_array( 'post_parent', $this->attributes, true ) )
			$this->post_parent_form( $post );

		do_action( self::ACTION_PREFIX . 'middle', $post );

		if ( in_array( 'menu_order', $this->attributes, true ) )
			$this->menu_order_form( $post );

		do_action( self::ACTION_PREFIX . 'bottom', $post );
	}

	/**
	 * @access private
	 *
	 * @see    https://github.com/mimosafa/WordPress/blob/4.1-branch/wp-admin/includes/meta-boxes.php#L710
	 *
	 * @param  WP_Post $post
	 * @return (void)
	 */
	private function post_parent_form( $post ) {
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
	private function menu_order_form( $post ) {
		$label = 'Order';
		$attr  = '';
		if ( $this->property && isset( $this->property->menu_order ) ) {
			$setting = $this->property->get_setting( 'menu_order' );
			$label = $setting['label'];
			if ( $setting['readonly'] )
				$attr .= ' readonly="readonly"';
		}
?>
<p><strong><?php _e( $label ) ?></strong></p>
<p>
  <label class="screen-reader-text" for="menu_order"><?php _e( $label ) ?></label>
  <input name="menu_order" type="number" id="menu_order" min="0" value="<?php echo esc_attr( $post->menu_order ) ?>" <?php echo $attr; ?>/>
</p>
<?php
	}

}
