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
	 * @var WP_Domain\{$domain}\property
	 */
	private $property;

	/**
	 * Constructor
	 *
	 * @access private
	 *
	 * @uses   WPDW\_property_object()
	 * @see    wp-domain-work/inc/functions.php
	 *
	 * @return  (void)
	 */
	private function __construct() {
		$this->domain = explode( '\\', __CLASS__ )[1];
		$this->property = \WPDW\_property_object( $this->domain );

		$this->init();
	}

	/**
	 * Initialize admin page
	 * 
	 * @access private
	 *
	 * @uses   WP_Domain\{$domain}\status
	 */
	private function init() {
		global $pagenow;
		if ( in_array( $pagenow, [ 'edit.php', 'post.php', 'post-new.php' ], true ) ) {
			/**
			 * Domain that registered as post_type
			 */
			$this->init_post_type( $pagenow );
		} else if ( $pagenow === 'edit-tags.php' ) {
			/**
			 * Domain that registered as taxonomy
			 */
			$this->init_taxonomy( $pagenow );
		}
		add_action( 'admin_enqueue_scripts', [ $this, 'scripts_handler' ] );
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
				$args = is_array( $this->attribute_meta_box ) ? $this->attribute_meta_box : [];
				$args = array_merge( [ 'attributes' => [ 'post_parent', 'menu_order' ] ], $args );
				new Admin\attribute_meta_box( $this->domain, $args );
			}
		}
		new Admin\save_post( $this->domain );
	}

	/**
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
			wp_enqueue_style( 'wpdw-post', \WPDW_PLUGIN_URL . '/css/post.css', [], '', 'screen' );
		}
	}

}
