<?php

namespace WP_Domain_Work\Admin\meta_boxes;

/**
 *
 */
class attributes_meta_box {
	use \WP_Domain_Work\Utility\Singleton;

	private $post_type;
	private static $attributes = [
		'post_parent' => [],
		'menu_order'  => []
	];

	protected function __construct() {
		if ( ! $this->post_type = get_post_type() ) {
			return false;
		}
		add_action( 'add_meta_boxes_' . $this->post_type, [ $this, 'init' ] );
	}

	/**
	 * @see https://github.com/WordPress/WordPress/blob/master/wp-admin/edit-form-advanced.php#L202
	 */
	public function init() {
		if ( self::$attributes = array_filter( self::$attributes ) ) {
			if ( post_type_supports( $this->post_type, 'page-attributes' ) ) {
				remove_meta_box( 'pageparentdiv', $this->post_type, 'side' );
				// warn ?
			}
			add_meta_box(
				$this->post_type . 'parentdiv',
				get_post_type_object( $this->post_type )->labels->name . __( 'Attributes' ),
				[ $this, 'meta_box' ],
				$this->post_type, 'side', 'core'
			);
		}
	}

	public static function set( $attr, $args ) {
		if ( ! array_key_exists( $attr, self::$attributes ) ) {
			return false;
		}
		if ( ! $_AMB = self::getInstance() ) {
			return false;
		}
		self::$attributes[$attr] = (array) $args;
	}

	//

	/**
	 * @see  https://github.com/WordPress/WordPress/blob/4.1-branch/wp-admin/includes/meta-boxes.php#L709
	 * @todo Change to using Walker class
	 * @todo If no contents, empty meta box is appered..
	 */
	public function meta_box( $post ) {
		if ( array_key_exists( 'post_parent', self::$attributes ) ) {
			$pparg = self::$attributes['post_parent'];
			$query_args = [
				'post_type' => $pparg['_post_type'],
				'posts_per_page' => -1,
				'order' => 'ASC',
				'orderby' => 'menu_order title',
			];
			$_posts = get_posts( $query_args );
			if ( ! empty( $_posts ) ) {
				$nowp = $pparg['value'];
?>
<p><strong><?php _e( $pparg['label'] ) ?></strong></p>
<label class="screen-reader-text" for="parent_id"><?php _e( $pparg['label'] ) ?></label>
<select name="parent_id" id="parent_id">
<?php
				foreach ( $_posts as $_post ) {
					$p_id = (int) $_post->ID;
					$p_title = get_the_title( $_post );
?>
  <option value="<?= $p_id ?>"<?= $p_id === $nowp ? ' selected' : '' ?>><?= esc_html( $p_title ) ?></option>
<?php
				}
?>
</select>
<?php
			}
		}
		if ( array_key_exists( 'menu_order', self::$attributes ) ) {
			$moarg = self::$attributes['menu_order'];
?>
<p><strong><?php _e( $moarg['label'] ) ?></strong></p>
<p><label class="screen-reader-text" for="menu_order"><?php _e( $moarg['label'] ) ?></label><input name="menu_order" type="text" size="4" id="menu_order" value="<?php echo esc_attr($post->menu_order) ?>" /></p>
<?php
		}
	}

}
