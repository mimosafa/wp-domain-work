<?php

namespace module;

/**
 * This is 'Trait',
 * must be used in '\(domain)\admin' class.
 *
 * @uses
 */
trait admin {

	/**
	 * @var string Dmain's name.
	 */
	private $domain;

	/**
	 * @var string post_type|taxonomy
	 */
	private $registered;

	/**
	 * @var string Registered name of domain on system.
	 */
	private $registeredName;

	/**
	 * 
	 */
	private $postParent = 0;

	/**
	 * 
	 */
	private $_box_id_prefix = 'wp-domain-work-meta-box-';

	/**
	 * @var null|object \(domain)\properties
	 */
	private static $properties = null;

	/**
	 * @var null|object \wordpress\admin\meta_box_inner
	 */
	private static $metaBoxInner = null;

	/**
	 * @var null|object \wordpress\admin\nonce
	 */
	private static $nonce = null;

	/**
	 * constructor
	 *
	 * @access public
	 */
	public function __construct() {
		$this->_domain_settings();
		# add_action( 'wp', [ $this, 'init' ] );
		add_action( 'add_meta_boxes', [ $this, 'init' ] );
	}

	public function init() {
		if ( ! $props =& $this->_get_properties() ) {
			return;
		}
		if ( 'post_type' === $this->registered ) {
			if ( doing_action( 'add_meta_boxes' ) && isset( $this->meta_boxes ) && $this->meta_boxes ) {
				foreach ( $this->meta_boxes as $arg ) {
					if ( is_string( $arg ) && in_array( $arg, [ 'post_parent', 'menu_order' ] ) && $prop = $props->$arg ) {
						\admin\meta_boxes\attributes_meta_box::set( $arg, $prop->getArray() );
					} else if ( is_array( $arg ) ) {
						if ( ! array_key_exists( 'property', $arg ) ) {
							continue;
						}
						$propName = $arg['property'];
						if ( ! $prop = $props->$propName ) {
							continue;
						}
						$id = esc_attr( $this->_box_id_prefix . $this->domain . '-' . $propName );
						$title = esc_html( $prop->label );
						if ( array_key_exists( 'callback', $arg ) && is_callable( $arg['callback'] ) ) {
							$callback = $arg['callback'];
						} else {
							if ( null === self::$metaBoxInner ) {
								self::$metaBoxInner = new \wordpress\admin\meta_box_inner( $this->registeredName );
							}
							$callback = [ self::$metaBoxInner, 'init' ];
						}
						$post_type = $this->registeredName;
						static $_contexts = [ 'normal', 'advanced', 'side' ];
						$context = array_key_exists( 'context', $arg ) && in_array( $arg['context'], $_contexts )
							? $arg['context']
							: 'advanced'
						;
						static $_priorities = [ 'high', 'core', 'default', 'low' ];
						$priority = array_key_exists( 'priority', $arg ) && in_array( $arg['priority'], $_priorities )
							? $arg['priority']
							: 'default'
						;
						$callback_args = [ 'instance' => $prop ];

						$meta_box = compact(
							'id', 'title', 'callback', 'post_type',
							'context', 'priority', 'callback_args'
						);
						call_user_func_array( 'add_meta_box', $meta_box );
					}
				}
				new \wordpress\admin\save_post( $this->registeredName );
			}
		}
	}

	/**
	 * Get domain's setting defined at properties.php (stored in option table as 'wp_dct_domains')
	 * 
	 * @access private
	 */
	private function _domain_settings() {
		/**
		 * define domain's name
		 *
		 * @uses \utility\getObjectNamespace
		 */
		$this->domain = \utility\getObjectNamespace( $this );

		/**
		 * Get domain's setting stored in option table
		 *
		 * @var array
		 */
		$setting = \WP_Domain_Work::get_domains()[$this->domain];

		// Identify 'post_type' or 'taxonomy'
		switch ( $setting['register'] ) {
			case 'Custom Post Type' :
				$this->registered = 'post_type';
				break;
			case 'Custom Taxonomy' :
				$this->registered = 'taxonomy';
				break;
		}

		// Confirm registered name
		if ( array_key_exists( $this->registered, $setting ) ) {
			$this->registeredName = $setting[$this->registered];
		} else {
			$this->registeredName = $this->domain;
		}
	}

	/**
	 * @return boolean
	 */
	private function &_get_properties() {
		if ( ! self::$properties ) {
			$className = sprintf( '\\%s\\properties', $this->domain );
			if ( ! class_exists( $className ) ) {
				return false;
			}
			self::$properties = new $className();
		}
		return self::$properties;
	}

	private function init_post_type() {
		// Post new as child post
		if (
			array_key_exists( 'post_parent', $_GET )
			&& ( $parent = get_post( $_GET['post_parent'] ) )
			&& current_user_can( 'edit_post', $parent )
		) {
			$this->postParent = (int) $_GET['post_parent'];
			if ( null === self::$nonce ) {
				self::$nonce = new \wordpress\admin\nonce( $this->domain );
			}
			add_action( 'edit_form_after_title', [ $this, 'view_post_parent' ] );
			add_filter( 'wp_insert_post_parent', [ $this, 'insert_post_parent' ], 10, 2 );
		}
	}

	/**
	 * Show parent post, and hidden form, for new post
	 * 
	 * @return (void)
	 */
	public function view_post_parent() {
		global $pagenow;
		if ( 'post-new.php' !== $pagenow ) {
			return;
		}
		$parent = get_post( $this->postParent );
		$parentType = get_post_type_object( $parent->post_type )->label;
		$ttl = get_the_title( $parent );
		$editLink = get_edit_post_link( $parent );
		?>
<div class="inside">
<p>Post as child post. Parent is <strong><?= sprintf( '<a href="%s">%s</a>', $editLink, $ttl ) ?></strong> ( <?= esc_html( $parentType ) ?> )</p>
<input type="hidden" name="post_parent" value="<?= $parent->ID ?>" />
<?= self::$nonce->nonce_field( 'post_parent' ) ?>
</div>
		<?php
	}

	/**
	 * Save new post with post_parent
	 *
	 * @see  https://developer.wordpress.org/reference/hooks/wp_insert_post_parent/
	 * 
	 * @param  int $post_parent
	 * @param  int $post_ID
	 * @return int
	 */
	public function insert_post_parent( $post_parent, $post_ID ) {
		if ( ! array_key_exists( 'post_parent', $_POST ) || ( ! $parent_id = absint( $_POST['post_parent'] ) ) ) {
			return $post_parent;
		}
		if ( ! self::$nonce->check_admin_referer( 'post_parent' ) ) {
			return $post_parent;
		}
		return $parent_id;
	}

}
