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
	 * Custom post type supports
	 * @var boolean
	 */
	private $post_type_supports = [
		/**
		 * Core supports
		 */
		'title'     => false,
		'editor'    => false,
		'author'    => false,
		'thumbnail' => false,
		'excerpt'   => false,
		'trackbacks'=> false,
		'custom_fields' => false,
		'comments'  => false,
		'revisions' => false,
		'page_attributes' => false,
		'post_formats' => false,
		/**
		 * Additional supports
		 */
		'slug' => false,
	];

	/**
	 * 
	 */
	private $_box_id_prefix = 'wp-dct-meta-box-';

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

		/**
		 * define domain's name
		 *
		 * @uses \utility\getObjectNamespace
		 */
		$this -> domain = \utility\getObjectNamespace( $this );

		$this -> _domain_setting();

		/**
		 * if contoroling some thing about class properties, define this function
		 */
		if ( method_exists( $this, 'custom_init' ) ) {
			$this -> custom_init();
		}

		if ( 'post_type' === $this -> registered ) {
			$this -> init_post_type();
		}

	}

	/**
	 * Get domain's setting defined at properties.php (stored in option table as 'wp_dct_domains')
	 * 
	 * @access private
	 */
	private function _domain_setting() {

		/**
		 * Get domain's setting stored in option table
		 *
		 * @var array
		 */
		$setting = get_option( 'wp_dct_domains' )[$this -> domain];

		/**
		 * Identify 'post_type' or 'taxonomy'
		 */
		switch ( $setting['register'] ) {
			case 'Custom Post Type' :
				$this -> registered = 'post_type';
				break;
			case 'Custom Taxonomy' :
				$this -> registered = 'taxonomy';
				break;
		}

		/**
		 * Confirm registered name
		 */
		if ( array_key_exists( $this -> registered, $setting ) ) {
			$this -> registeredName = $setting[$this -> registered];
		} else {
			$this -> registeredName = $this -> domain;
		}

	}

	private function init_post_type() {

		/**
		 * Post type supports
		 */
		
		if ( $default = get_option( 'wp_dct_post_type_default_supports' ) ) {
			//
		}

		foreach ( $this -> post_type_supports as $support => $bool ) {
			$_support = '_' . $support;
			if ( property_exists( $this, $_support ) ) {
				$this -> post_type_supports[$support] = $this -> $_support;
			}
		}

		if ( $this -> post_type_supports = array_filter( $this -> post_type_supports ) ) {
			$this -> add_post_type_supports();
		}

		/**
		 * Post type meta boxes
		 */
		
		if ( isset( $this -> meta_boxes ) && !empty( $this -> meta_boxes ) ) {

			/**
			 * Add meta boxes
			 */
			add_action( 'add_meta_boxes', [ $this, 'post_type_meta_boxes' ] );

			/**
			 * Save post
			 */
			new \wordpress\admin\save_post( $this -> registeredName );

		}

		/**
		 * Post new as child post
		 */
		if (
			array_key_exists( 'post_parent', $_GET )
			&& ( $parent = get_post( $_GET['post_parent'] ) )
			&& current_user_can( 'edit_post', $parent )
		) {
			$this -> postParent = (int) $_GET['post_parent'];
			if ( null === self::$nonce ) {
				self::$nonce = new \wordpress\admin\nonce( $this -> domain );
			}
			add_action( 'edit_form_after_title', [ $this, 'view_post_parent' ] );
			add_filter( 'wp_insert_post_parent', [ $this, 'insert_post_parent' ], 10, 2 );
		}

	}

	/**
	 * Add post type support, if necessary show as readonly form
	 */
	private function add_post_type_supports() {
		foreach ( $this -> post_type_supports as $support => $param ) {
			if ( true === $param ) {
				if ( 'slug' === $support ) {
					continue;
				}
				add_post_type_support( $this -> registeredName, str_replace( '_', '-', $support ) );
			} else if ( 'readonly' === $param ) {
				$className = '\\wordpress\\admin\\post_type\\readonly_' . $support;
				if ( class_exists( $className ) ) {
					$className::set( $this -> registeredName );
				}
			}
		}
	}

	/**
	 *
	 */
	public function post_type_meta_boxes() {

		/**
		 * @uses \module\admin::_properties()
		 */
		if ( !$this -> _properties() ) {
			return;
		}

		foreach ( $this -> meta_boxes as $arg ) {

			if ( !array_key_exists( 'property', $arg ) ) {
				continue;
			}

			$propertyName = $arg['property'];

			if ( !$property = self::$properties -> $propertyName ) {
				continue;
			}

			/**
			 * Set arguments of function 'add_meta_box'
			 */
			
			$id = esc_attr( $this -> _box_id_prefix . $this -> domain . '-' . $propertyName );

			$title = esc_html( $property -> label );

			// callback
			if ( array_key_exists( 'callback', $arg ) && is_callable( $arg['callback'] ) ) {
				$callback = $arg['callback'];
			} else {
				/**
				 * get \wprdpress\admin\meta_box_inner instance
				 */
				if ( null === self::$metaBoxInner ) {
					self::$metaBoxInner = new \wordpress\admin\meta_box_inner( $this -> registeredName );
				}
				$callback = [ self::$metaBoxInner, 'init' ];
			}

			$post_type = $this -> registeredName;

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

			$callback_args = [ 'instance' => $property ];

			$meta_box = compact(
				'id', 'title', 'callback', 'post_type',
				'context', 'priority', 'callback_args'
			);

			call_user_func_array( 'add_meta_box', $meta_box );

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
		$parent = get_post( $this -> postParent );
		$parentType = get_post_type_object( $parent -> post_type ) -> label;
		$ttl = get_the_title( $parent );
		$editLink = get_edit_post_link( $parent );
		?>
<div class="inside">
<p>Post as child post. Parent is <strong><?= sprintf( '<a href="%s">%s</a>', $editLink, $ttl ) ?></strong> ( <?= esc_html( $parentType ) ?> )</p>
<input type="hidden" name="post_parent" value="<?= $parent -> ID ?>" />
<?= self::$nonce -> nonce_field( 'post_parent' ) ?>
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
		if ( !array_key_exists( 'post_parent', $_POST ) || ( !$parent_id = absint( $_POST['post_parent'] ) ) ) {
			return $post_parent;
		}
		if ( !self::$nonce -> check_admin_referer( 'post_parent' ) ) {
			return $post_parent;
		}
		return $parent_id;
	}

	/**
	 * Check domain's properties class (\(domain)\properties) exists
	 * if class exists and instance yet, get instance
	 *
	 * @uses   \(domain)\properties
	 * @return bool
	 */
	private function _properties() {
		if ( null !== self::$properties ) {
			return true;
		}
		$className = '\\' . $this -> domain . '\\properties';
		if ( class_exists( $className ) ) {
			self::$properties = new $className();
			return true;
		}
		return false;
	}

}
