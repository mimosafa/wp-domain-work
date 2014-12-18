<?php

namespace service\domain;

/**
 * Class for init domains
 */
class init {

	/**
	 * Instance for registering custom post types & custom taxonomies
	 *
	 * @var null | object \wordpress\register_customs
	 */
	private static $registerCustoms = null;

	/**
	 * Instance for change rewrite rules slugs to post ids
	 *
	 * @var null | object \wordpress\int_permalink
	 */
	private static $intPermalink = null;

	/**
	 * Instance for creating original endpoints
	 *
	 * @var null | object \wordpress\create_endpoints
	 */
	private static $createEndpoints = null;

	/**
	 * 
	 */
	private static $roles = null;

	/**
	 * Default arguments for registering custom post type
	 * (Given priority than contents of the properties.php)
	 */
	private $_cpt_option = [
		'public'       => true,
		'has_archive'  => true,
		'hierarchical' => false,
		'rewrite'      => [ 'with_front' => false ],
		'supports'     => false,
	];

	/**
	 * Default arguments for registering custom taxonomy
	 * (Given priority than contents of the properties.php)
	 */
	private $_ct_option = [
		'public'    => true,
		'query_var' => true,
		'rewrite'   => [ 'with_front' => false ],
	];

	/**
	 * @todo  tracking domains directories flag
	 */
	public function __construct() {

		/**
		 * scan domain directories class
		 *
		 * @uses  \service\domain\directories
		 */
		$directories = new directories();

		if ( !$domains = $directories -> getDomains() ) {
			return;
		}

		$this -> _classfy_domains( $domains );

		$this -> init();

		// ClassLoader
		if ( $classloaders = $directories -> getClassloaders() ) {
			foreach ( $classloaders as $domain => $somePath ) {
				foreach ( $somePath as $path ) {
					\ClassLoader::register( $domain, $path );
				}
			}
		}

		// Include functions.php
		if ( $functionsFiles = $directories -> getFunctionsFiles() ) {
			foreach ( $functionsFiles as $functions ) {
				require_once $functions;
			}
		}

		if ( $domains !== get_option( 'wp_dct_domains' ) ) {

			/**
			 * Display message
			 */
			add_action( 'admin_notices', function() {
				echo '<div class="message updated">';
				echo '<p>Domain is updated.</p>';
				echo '</div>';
			} );

			$this -> _flush_rewrite_rules();
			update_option( 'wp_dct_domains', $domains );
			
		}

	}

	/**
	 * @access private
	 *
	 * @param  array $domains (required)
	 * @return (void)
	 */
	private function _classfy_domains( Array $domains ) {
		foreach ( $domains as $domain => $array ) {
			if ( 'Custom Post Type' === $array['register'] ) {
				$this -> _cpt_setting( $domain, $array );
			} elseif ( 'Custom Taxonomy' === $array['register'] ) {
				$this -> _ct_setting( $domain, $array );
			} elseif ( 'Custom Endpoint' === $array['register'] ) {
				$this -> _ep_setting( $domain, $array ); // yet!
			} else {
				unset( $this -> domains[$domain] );
			}
		}
	}

	/**
	 * @access private
	 *
	 * @uses \wordpress\register_customs
	 * @uses \wordpress\int_permalink
	 *
	 * @param  string $domain (required)
	 * @param  array $array (required)
	 */
	private function _cpt_setting( $domain, Array $array ) {

		/**
		 * Name (slug)
		 */
		$post_type = ( array_key_exists( 'post_type', $array ) )
			? esc_html( $array['post_type'] )
			: esc_html( $domain )
		;

		/**
		 * Label
		 */
		$label = ( array_key_exists( 'name', $array ) )
			? esc_html( $array['name'] )
			: ucwords( str_replace( '_', ' ', $post_type ) )
		;

		/**
		 * Options (rewrite, capability_type, )
		 */
		
		/**
		 * Rewrite slug
		 */
		$opt = [ 'rewrite' => [ 'slug' => $domain ] ];

		/**
		 * Capability type
		 */
		if ( array_key_exists( 'capability_type', $array ) ) {
			$cap_type = array_map( function( $var ) {
				return trim( $var );
			}, explode( ',', $array['capability_type'] ) );
			if ( 2 === count( $cap_type ) ) {
				$opt['capability_type'] = $cap_type;
				$opt['map_meta_cap'] = true;
				/**
				 * 
				 */
				if ( null === self::$roles ) {
					self::$roles = new \wordpress\roles();
				}
				self::$roles -> add_cap( $cap_type );
			}
		}

		/**
		 * Merge default setting to each post type setting
		 */
		$opt = \utility\md_array_merge( $opt, $this -> _cpt_option );

		/**
		 * Get instance \wordpress\register_customs, if not constructed.
		 */
		if ( null === self::$registerCustoms ) {
			self::$registerCustoms = new \wordpress\register_customs();
		}

		self::$registerCustoms -> add_post_type( $post_type, $label, $opt );

		/**
		 * 
		 */
		if ( array_key_exists( 'rewrite', $array ) && 'ID' === $array['rewrite'] ) {

			/**
			 * Get instance \wordpress\int_permalink, if not constructed.
			 */
			if ( null === self::$intPermalink ) {
				self::$intPermalink = new \wordpress\int_permalink();
			}
			self::$intPermalink -> set( $post_type );

		}
	}

	/**
	 * @access private
	 *
	 * @uses \wordpress\register_customs
	 *
	 * @param  string $domain (required)
	 * @param  array $array (required)
	 */
	private function _ct_setting( $domain, Array $array ) {
		// Taxonomy register name
		$taxonomy = array_key_exists( 'taxonomy', $array )
			? esc_html( $array['taxonomy'] )
			: esc_html( $domain )
		;
		// Label
		$label = array_key_exists( 'name', $array )
			? esc_html( $array['name'] )
			: ucwords( str_replace( '_', ' ', $taxonomy ) )
		;
		// Post types
		$post_types = explode( ',', $array['related_post_type'] );
		$post_types = array_map( function( $string ) {
			return trim( $string );
		}, $post_types );
		// Rewrite
		$opt = [ 'rewrite' => [ 'slug' => $domain ] ];
		if ( array_key_exists( 'taxonomy_type', $array ) && 'Category' === $array['taxonomy_type'] ) {
			$opt['hierarchical'] = true;
			$opt['rewrite']['hierarchical'] = true;
		}
		// ~
		$opt = \utility\md_array_merge( $opt, $this -> _ct_option );

		/**
		 * Get instance \wordpress\register_customs, if not constructed.
		 */
		if ( null === self::$registerCustoms ) {
			self::$registerCustoms = new \wordpress\register_customs();
		}

		self::$registerCustoms -> add_taxonomy( $taxonomy, $label, $post_types, $opt );
	}

	/**
	 *
	 */
	private function _ep_setting( $domain, $array ) {
		if ( null === self::$createEndpoints ) {
			self::$createEndpoints = new \wordpress\create_endpoints();
		}
		// ~ yet!!
		self::$createEndpoints -> set( $domain );
	}

	/**
	 * 
	 * @access private
	 */
	private function init() {

		// Custom post types & Custom taxonomies
		if ( null !== self::$registerCustoms ) {
			self::$registerCustoms -> init();
		}

		// Rewrite slug to post_id
		if ( null !== self::$intPermalink ) {
			self::$intPermalink -> init();
		}

		// Customs endpoint
		if ( null !== self::$createEndpoints ) {
			self::$createEndpoints -> init();
		}

		if ( null !== self::$roles ) {
			self::$roles -> init();
		}

	}

	/**
	 *
	 */
	public function _flush_rewrite_rules() {
		/**
		 * Flush rewrite rules
		 */
		add_action( 'init', function() {
			flush_rewrite_rules();
		}, 99 );
	}

	/**
	 * 
	 */
	private function add_editor_caps() {
		//
	}

}















