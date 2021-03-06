<?php
namespace WPDW\Device;

/**
 * @uses   WPDW\Device\Module\Methods::is()
 * @uses   WPDW\Device\Module\Methods::isDefined()
 * @global $pagenow
 */
trait admin {
	use \WPDW\Util\Singleton, Module\Methods;

	/**
	 * @var string
	 */
	private $domain;

	/**
	 * Constructor
	 *
	 * @access private
	 *
	 * @return  (void)
	 */
	private function __construct() {
		$this->domain = explode( '\\', __CLASS__ )[1];

		global $pagenow;
		if ( in_array( $pagenow, [ 'edit.php', 'post.php', 'post-new.php' ], true ) ) {
			$this->init_post_type( $pagenow );
		} else if ( $pagenow === 'edit-tags.php' ) {
			$this->init_taxonomy( $pagenow );
		}

		/**
		 * Handle stylesheet and scripts
		 */
		add_action( 'admin_enqueue_scripts', [ &$this, 'scripts_handler' ] );
	}

	/**
	 * @access private
	 *
	 * @param  string $pagenow
	 * @return (void)
	 */
	private function init_post_type( $pagenow ) {
		if ( $pagenow === 'edit.php' ) {
			if ( $this->isDefined( 'columns' ) ) {
				/**
				 * Post columns
				 * @uses  WPDW\Device\Admin\posts_columns
				 */
				$columns = new Admin\posts_columns( $this->domain );
				foreach ( $this->columns as $name => $args )
					$columns->add( $name, $args );
			}
		} else {
			if ( $this->isDefined( 'meta_boxes' ) ) {
				/**
				 * Meta boxes
				 * @uses  WPDW\Device\Admin\meta_boxes
				 */
				$metabox = new Admin\meta_boxes( $this->domain );
				foreach ( $this->meta_boxes as $meta_box_args )
					$metabox->add( $meta_box_args );
			}
			if ( $this->isDefined( 'edit_forms' ) ) {
				/**
				 * Edit forms
				 * @uses  WPDW\Device\Admin\edit_form_advanced
				 */
				$editForm = new Admin\edit_form_advanced( $this->domain );
				foreach ( $this->edit_forms as $edit_form_args )
					$editForm->add( $edit_form_args );
			}
			if ( $this->is( 'attribute_meta_box' ) ) {
				/**
				 * Attribute meta box
				 * @uses  WPDW\Device\Admin\attribute_meta_box
				 */
				$attrBox = new Admin\attribute_meta_box( $this->domain );
				if ( is_array( $this->attribute_meta_box ) && $this->attribute_meta_box ) {
					if ( isset( $this->attribute_meta_box['title'] ) && $title = filter_var( $this->attribute_meta_box['title'] ) )
						$attrBox->title = esc_attr( $title );
					for ( $i = 0; isset( $this->attribute_meta_box[$i] ) ; $i++ ) {
						$attrBox->add( (array) $this->attribute_meta_box[$i] );
					}
				}
			}
		}
		new Admin\save_post( $this->domain );
	}

	/**
	 * @todo
	 *
	 * @access private
	 */
	private function init_taxonomy( $pagenow ) {
		//
	}

	/**
	 * @access public
	 * 
	 * @param  string $pagenow
	 * @return (void)
	 */
	public function scripts_handler( $pagenow ) {
		global $pagenow;
		if ( in_array( $pagenow, [ 'post.php', 'post-new.php'], true ) ) {
			wp_enqueue_style( 'wpdw-post', \WPDW_PLUGIN_URL . '/css/admin-post.css', [], '', 'screen' );
			wp_enqueue_script( 'wpdw-post', \WPDW_PLUGIN_URL . '/js/admin-post.js', [ 'wpdw', 'backbone' ], '', true );
		}
	}

}
