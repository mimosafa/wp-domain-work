<?php

namespace wordpress\admin;

class settings_page {

	/**
	 * Capability of read settings page
	 *
	 * @var string
	 */
	private $capability = 'manage_options';

	/**
	 * @var string
	 */
	private $icon_url = '';

	/**
	 * @var integer
	 */
	private $position = null;

	/**
	 * @var array
	 */
	private $page = [];

	/**
	 * argument caches
	 *
	 * @var array
	 */
	private static $section = [];
	private static $field   = [];

	/**
	 * arrays for function's arguments
	 * 
	 * @var array
	 */
	private $sections = [];
	private $fields   = [];
	private $settings = [];

	/**
	 * Set capability
	 *
	 * @param  string $capability
	 * @return (void)
	 */
	public function capability( $capability ) {
		if ( $capability && is_string( $capability ) ) {
			$this -> capability = $capability;
		}
	}

	/**
	 * Set icon_url
	 *
	 * @param  string $icon_url
	 * @return (void)
	 */
	public function icon_url( $icon_url ) {
		if ( $icon_url && is_string( $icon_url ) ) {
			$this -> icon_url = $icon_url;
		}
	}

	/**
	 * Set position in admin menu
	 *
	 * @param  integer $position
	 * @return (void)
	 */
	public function position( $position ) {
		if ( $position && $int = absint( $position ) ) {
			$this -> position = $int;
		}
	}

	/**
	 * @return object WP_Domain_Work
	 */
	public function page( $menu_slug, $page_title = '', $menu_title = '' ) {
		if ( !$menu_slug || !is_string( $menu_slug ) ) {
			return;
		}
		$arg =& $this -> page;
		$arg['menu_slug'] = $menu_slug;
		if ( $page_title && is_string( $page_title ) ) {
			$arg['page_title'] = $page_title;
		}
		if ( $menu_title && is_string( $menu_title ) ) {
			$arg['menu_title'] = $menu_title;
		}
		$arg['sections']   = [];
		return $this;
	}

	/**
	 * @return object WP_Domain_Work
	 */
	public function section( $section_id, $section_title = '', $description = '' ) {
		/**
		 * static $field cache init
		 */
		if ( !empty( self::$field ) ) {
			self::$section['fields'][] = self::$field;
			self::$field = [];
		}
		/**
		 * static $section cache init
		 */
		if ( !empty( self::$section ) ) {
			$this -> page['sections'][] = self::$section;
			self::$section = [];
		}
		if ( !$section_id || !is_string( $section_id ) ) {
			return $this;
		}
		self::$section['section_id'] = $section_id;
		if ( !$section_title || !is_string( $section_title ) ) {
			$section_title = ucwords( trim( str_replace( [ '-', '_' ], ' ', $section_id ) ) );
		}
		self::$section['title'] = $section_title;
		if ( $description ) {
			self::$section['description'] = $description;
		}
		self::$section['fields'] = [];
		return $this;
	}

	/**
	 * @return object WP_Domain_Work
	 */
	public function field( $field_id, $callback, $option_name = '', $field_title = '', $args = [] ) {
		/**
		 * static $field cache init
		 */
		if ( !empty( self::$field ) ) {
			self::$section['fields'][] = self::$field;
			self::$field = [];
		}
		if ( !$field_id || !is_string( $field_id ) ) {
			return $this;
		}
		if ( !method_exists( $this, $callback ) && !is_callable( $callback ) ) {
			return $this;
		}
		self::$field['field_id'] = $field_id;
		self::$field['callback'] = $callback;
		if ( $option_name ) {
			self::$field['option_name'] = $option_name;
		}
		if ( !$field_title || !is_string( $field_title ) ) {
			$field_title = ucwords( trim( str_replace( [ '-', '_' ], ' ', $field_id ) ) );
		}
		self::$field['title'] = $field_title;
		return $this;
	}

	/**
	 *
	 */
	public function init( $arg = [] ) {
		/**
		 * static $field cache init
		 */
		if ( !empty( self::$field ) ) {
			self::$section['fields'][] = self::$field;
			self::$field = [];
		}
		/**
		 * static $section cache init
		 */
		if ( !empty( self::$section ) ) {
			$this -> page['sections'][] = self::$section;
			self::$section = [];
		}
		/*
		if ( $arg && is_array( $arg ) && !\utility\is_vector( $arg ) ) {
			$this -> pages = $arg;
		}
		if ( $this -> pages ) {
			add_action( 'admin_menu', [ $this, 'add_pages' ] );
			add_action( 'admin_init', [ $this, 'add_settings' ] );
		}
		*/
	}

	/**
	 * 
	 */
	public function add_pages() {

		/**
		 * pages
		 */
		foreach ( $this -> pages as $menu_slug => $page_args ) {
			/**
			 * Page title
			 */
			if ( array_key_exists( 'page_title', $page_args ) ) {
				$page_title = esc_html( $page_args['page_title'] );
			} else {
				$page_title = ucwords( trim( str_replace( [ '-', '_', '/', '.php' ], ' ', $menu_slug ) ) );
			}

			/**
			 * Menu title
			 */
			if ( array_key_exists( 'menu_title', $page_args ) ) {
				$menu_title = esc_html( $page_args['menu_title'] );
			} else {
				$menu_title = $page_title;
			}
			
			add_menu_page(
				$page_title, $menu_title, $this -> capability, $menu_slug, [ $this, 'page_body' ], $this -> icon_url, $this -> position
			);

			/**
			 * option group
			 */
			$optionGroup = 'group_' . $menu_slug;

			/**
			 * sections
			 */
			$sections = $page_args['sections'];
			foreach ( $sections as $section_id => $section_args ) {
				$section_title = esc_html( $section_args['title'] );
				$callback = array_key_exists( 'callback', $section_args ) && is_callable( $section_args['callback'] )
					? $section_args['callback'] : ''
				;
				$this -> sections[] = [ $section_id, $section_title, $callback, $menu_slug ];

				/**
				 * fields
				 */
				$fields = $section_args['fields'];
				foreach ( $fields as $field_id => $field_args ) {
					$field_title = esc_html( $field_args['title'] );
					$callback = [ $this, $field_args['callback'] ];
					$args = compact( 'menu_slug', 'section_id', 'field_id' ) + $field_args;
					$this -> fields[] = [ $field_id, $field_title, $callback, $menu_slug, $section_id, $args ];
					$this -> settings[] = [ $optionGroup, $field_args['option_name'], '' ];
				}
			}
		}

		_var_dump( $menu );
		_var_dump( $admin_page_hooks );
		_var_dump( $_registered_pages );
		_var_dump( $_parent_pages );

	}

	/**
	 * Drowing page html
	 * 
	 * @return (void)
	 */
	public function page_body() {
		$menu_slug = $_GET['page'];
		$h2 = esc_html( $this -> pages[$menu_slug]['page_title'] );
		$optionGroup = 'group_' . $menu_slug;
		?>
<div class="wrap">
  <h2><?= $h2 ?></h2>
  <form method="post" action="options.php">
    <?php settings_fields( $optionGroup ); ?>
    <?php do_settings_sections( $menu_slug ); ?>
    <?php submit_button(); ?>
  </form>
</div>
		<?php
	}

	/**
	 * 
	 */
	public function add_settings() {
		foreach ( $this -> sections as $section_arg ) {
			call_user_func_array( 'add_settings_section', $section_arg );
		}
		foreach ( $this -> fields as $field_arg ) {
			call_user_func_array( 'add_settings_field', $field_arg );
		}
		foreach ( $this -> settings as $setting_arg ) {
			call_user_func_array( 'register_setting', $setting_arg );
		}
	}

	public function checkbox( $array ) {
		$args = $this -> pages[$array['menu_slug']];
		if ( array_key_exists( 'section_id', $array ) ) {
			$args = $args['sections'][$array['section_id']];
		}
		$args = $args['fields'][$array['field_id']];
		$option_name = esc_attr( $args['option_name'] );
		$checked = get_option( $args['option_name'] ) ? 'checked="checked" ' : '';
		$label = array_key_exists( 'label', $array ) ? __( esc_html( $array['label'] ) ) : '';
		$description = array_key_exists( 'description', $array ) ? $array['description'] : '';
		?>
<fieldset>
  <label for="<?= $option_name ?>">
    <input type="checkbox" name="<?= $option_name ?>" id="<?= $option_name ?>" value="1" <?= $checked ?>/>
    <?= $label ?>
  </label>
  <?php if ( $description ) { ?>
  <p class="description"><?= $description ?></p>
  <?php } ?>
</fieldset>
		<?php
	}

}
