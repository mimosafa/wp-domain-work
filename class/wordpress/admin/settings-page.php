<?php

namespace wordpress\admin;

/**
 * CAUTION: This class methods print basically non-escape text.
 *
 */
class settings_page {

	/**
	 * Settings page structure
	 *
	 * @var array
	 */
	private $page = [];

	/**
	 * Argument caches
	 *
	 * @var array
	 */
	private static $section;
	private static $field;

	/**
	 * Arguments of 'add_settings' method 
	 * 
	 * @var array
	 */
	private $sections = [];
	private $fields   = [];
	private $settings = [];

	/**
	 * Arguments of callback functions
	 *
	 * @var array
	 */
	private $callback_args = [];

	/**
	 * Constructor
	 */
	public function __construct() {
		$this -> init();
	}

	/**
	 * Initialize instance
	 *
	 * @access public
	 *
	 * @return $this
	 */
	public function init() {
		$this -> init_field();
		$this -> init_section();
		if ( $this -> page ) {
			add_action( 'admin_menu', [ $this, 'add_page' ] );
			add_action( 'admin_init', [ $this, 'add_settings' ] );
			//_var_dump( $this -> page );
		}
	}

	/**
	 * Initialize static cache $section
	 *
	 * @access private
	 */
	private function init_section() {
		if ( !empty( self::$section ) ) {
			if ( $this -> page ) {
				if ( !array_key_exists( 'sections', $this -> page ) ) {
					$this -> page['sections'] = [];
				}
				$this -> page['sections'][] = self::$section;
			}
		}
		self::$section = [];
	}

	/**
	 * Initialize static cache $field
	 *
	 * @access private
	 */
	private function init_field() {
		if ( !empty( self::$field ) ) {
			if ( self::$section ) {
				if ( !array_key_exists( 'fields', self::$section ) ) {
					self::$section['fields'] = [];
				}
				self::$section['fields'][] = self::$field;
			}
		}
		self::$field = [];
	}

	/**
	 * Adding page (run in action hook 'admin_menu')
	 *
	 * @access public
	 */
	public function add_page() {
		if ( !doing_action( 'admin_menu' ) || !$this -> page || !array_key_exists( 'page', $this -> page ) ) {
			return;
		}
		global $admin_page_hooks;

		extract( $this -> page ); // $page must be generated.

		if ( !isset( $page_title ) ) {
			$page_title = ucwords( trim( str_replace( [ '-', '_', '/', '.php' ], ' ', $page ) ) );
		}
		if ( !isset( $menu_title ) ) {
			$menu_title = $page_title;
		}
		if ( !isset( $capability ) ) {
			$capability = 'manage_options';
		}
		if ( !isset( $callback ) ) {
			$callback = [ $this, 'page_body' ];
		}

		if ( !array_key_exists( $page, $admin_page_hooks ) ) {

			if ( !isset( $icon_url ) ) {
				$icon_url = '';
			}
			if ( !isset( $position ) ) {
				$position = null;
			}
			add_menu_page( $page_title, $menu_title, $capability, $page, $callback, $icon_url, $position );

		} else {

			if ( !isset( $sub_page ) ) {
				return; // throw error
			}
			add_submenu_page( $page, $page_title, $menu_title, $capability, $sub_page, $callback );

		}

		$menu_slug = isset( $sub_page ) ? $sub_page : $page;

		if ( isset( $sections ) && $sections ) {
			$this -> add_sections( $sections, $menu_slug );
		}

		if ( isset( $fields ) && $fields ) {
			$this -> add_fields( $fields, $menu_slug );
		}

			/**
			 * sections
			 */
			/*
			foreach ( $sections as $section_args ) {
				$section_id = $section_args['id'];
				if ( array_key_exists( 'title', $section_args ) ) {
					$section_title = $section_args['title'];
				} else {
					$section_title = ucwords( str_replace( [ '-', '_' ], ' ', $section_id ) );
				}
				if ( array_key_exists( 'callback', $section_args ) ) {
					$callback = $section_srgs['callback'];
				} else {
					$callback = [ $this, 'section_body' ];
				}
				$this -> sections[] = [ $section_id, $section_title, $callback, $menu_slug ];
				$this -> callback_args[$section_id] = $section_args;

				/**
				 * fields
				 */

				/*
				$fields = $section_args['fields'];
				foreach ( $fields as $field_id => $field_args ) {
					$field_title = esc_html( $field_args['title'] );
					$callback = [ $this, $field_args['callback'] ];
					$args = compact( 'menu_slug', 'section_id', 'field_id' ) + $field_args;
					$this -> fields[] = [ $field_id, $field_title, $callback, $menu_slug, $section_id, $args ];
					$this -> settings[] = [ $optionGroup, $field_args['option_name'], '' ];
				}
				*/
			#}

		#}
	}

	private function add_fields( $fields, $menu_slug, $section_id = '' ) {
		if ( !$fields || !$menu_slug || !is_array( $fields ) ) {
			return;
		}
		$option_group = 'group_' . $menu_slug;
		foreach ( $fields as $field ) {
			if ( !array_key_exists( 'id', $field ) || !array_key_exists( 'callback', $field ) ) {
				continue;
			}
			$id = $field['id'];
			$callback = $field['callback'];
			if ( array_key_exists( 'title', $field ) ) {
				$title = $field['title'];
			} else {
				$title = ucwords( str_replace( [ '-', '_' ], ' ', $id ) );
			}
			$this -> fields[] = [ $id, $title, $callback, $menu_slug, $section_id, $field ];
			if ( array_key_exists( 'option_name', $field ) ) {
				$sanitize = array_key_exists( 'sanitize', $field ) && ( method_exists( $this, $field['sanitize'] ) || is_callable( $field['sanitize'] ) )
					? $field['sanitize']
					: ''
				;
				$this -> settings[] = [ $option_group, $field['option_name'], $sanitize ];
			}
		}
	}

	/**
	 * Adding section
	 *
	 * @access private
	 */
	private function add_sections( $sections, $menu_slug ) {
		if ( !$sections || !$menu_slug || !is_array( $sections ) ) {
			return;
		}
		foreach ( $sections as $section ) {
			extract( $section );
			if ( !isset( $id ) ) {
				continue;
			}
			if ( !isset( $title ) ) {
				$title = ucwords( str_replace( [ '-', '_' ], ' ', $id ) );
			}
			if ( !isset( $callback ) ) {
				$callback = [ $this, 'section_body' ];
			}
			$this -> sections[] = [ $id, $title, $callback, $menu_slug ];
			$this -> callback_args[$id] = $section;

			if ( isset( $fields ) && $fields ) {
				$this -> add_fields( $fields, $menu_slug, $id );
			}
		}
	}

	/**
	 * Setting sections & fields method (run in action hook 'admin_init')
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

	/**
	 * (Required) Set top level menu page
	 *
	 * @access public
	 *
	 * @param  string $page (optional) if empty 'options.php' set
	 * @param  string $page_title (optional)
	 * @param  string $menu_title (optional)
	 * @return $this
	 */
	public function page( $page = null, $page_title = null, $menu_title = null ) {
		if ( $this -> page ) {
			return; // error: you must initialize instance
		}
		$this -> page['page'] = $page && is_string( $page ) ? $page : 'options.php';
		if ( $page_title ) {
			$this -> page_title( $page_title );
		}
		if ( $menu_title ) {
			$this -> menu_title( $menu_title );
		}
		return $this;
	}

	/**
	 * Set submenu page
	 * This must be set if you set page (using page() method) exists menu page slug as parent page.
	 *
	 * @access public
	 *
	 * @param  string $sub_page (required)
	 * @param  string $page_title (optional)
	 * @param  string $menu_title (optional)
	 * @return $this
	 */
	public function sub_page( $sub_page, $page_title = null, $menu_title = null ) {
		if ( $sub_page && is_string( $sub_page ) ) {
			$this -> page['sub_page'] = $sub_page;
		}
		if ( $page_title ) {
			$this -> page_title( $page_title );
		}
		if ( $menu_title ) {
			$this -> menu_title( $menu_title );
		}
		return $this;
	}

	/**
	 * Set page title
	 *
	 * @access public
	 *
	 * @param  string $title
	 * @return $this
	 */
	public function page_title( $page_title ) {
		if ( $page_title && is_string( $page_title ) ) {
			$this -> page['page_title'] = $page_title;
		}
		return $this;
	}

	/**
	 * Set menu title
	 *
	 * @access public
	 *
	 * @param  string $title
	 * @return $this
	 */
	public function menu_title( $menu_title ) {
		if ( $menu_title && is_string( $menu_title ) ) {
			$this -> page['menu_title'] = $menu_title;
		}
		return $this;
	}

	/**
	 * Set capability
	 *
	 * @access public
	 *
	 * @param  string $capability
	 * @return $this
	 */
	public function capability( $capability ) {
		if ( $capability && is_string( $capability ) ) {
			$this -> page['capability'] = $capability;
		}
		return $this;
	}

	/**
	 * Set icon_url
	 *
	 * @param  string $icon_url
	 * @return $this
	 */
	public function icon_url( $icon_url ) {
		if ( $icon_url && is_string( $icon_url ) ) {
			$this -> page['icon_url'] = $icon_url;
		}
		return $this;
	}

	/**
	 * Set position in admin menu
	 *
	 * @param  integer $position
	 * @return $this
	 */
	public function position( $position ) {
		if ( $position && $int = absint( $position ) ) {
			$this -> page['position'] = $int;
		}
		return $this;
	}

	/**
	 * Set description text
	 * Description will be contained initialized cache(field, section, page) before calling this method
	 *
	 * @access public
	 *
	 * @param  string $text
	 * @param  bool   $esc
	 * @return $this|(void)
	 */
	public function description( $text, $wrap_p = true , $esc = false ) {
		if ( !$text || !is_string( $text ) ) {
			return;
		}
		if ( self::$field ) {
			$cache =& self::$field;
		} else if ( self::$section ) {
			$cache =& self::$section;
		} else if ( $this -> page ) {
			$cache =& $this -> page;
		} else {
			return;
		}
		$desc = !$esc ? $text : esc_html( $text );
		if ( $wrap_p ) {
			$desc = '<p>' . $desc . '</p>';
		}
		$cache['description'] = $desc;
		return $this;
	}

	/**
	 * Set callback function
	 * Callback will be contained initialized cache(field, section, page) before calling this method
	 *
	 * @access public
	 *
	 * @param  string $callback
	 * @return $this|(void)
	 */
	public function callback( $callback ) {
		if ( !method_exists( $this, $callback ) && !is_callable( $callback ) ) {
			return;
		}
		if ( self::$field ) {
			$cache =& self::$field;
		} else if ( self::$section ) {
			$cache =& self::$section;
		} else if ( $this -> page ) {
			$cache =& $this -> page;
		} else {
			return;
		}
		$cache['callback'] = method_exists( $this, $callback ) ? [ $this, $callback ] : $callback;
		return $this;
	}

	/**
	 * Initialize & set section
	 *
	 * @access public
	 *
	 * @return $this
	 */
	public function section( $section_id, $section_title = null ) {
		$this -> init_field();
		$this -> init_section();

		if ( !$section_id || !is_string( $section_id ) ) {
			return;
		}
		self::$section['id'] = $section_id;
		if ( $section_title && is_string( $section_title ) ) {
			self::$section['title'] = $section_title;
		}
		return $this;
	}

	/**
	 * @return object
	 */
	public function field( $field_id, $callback, $field_title = null, $option_name = null ) {
		$this -> init_field();

		if ( !$field_id || !is_string( $field_id ) || !$callback ) {
			return $this;
		}
		self::$field['id'] = $field_id;
		if ( !method_exists( $this, $callback ) && !is_callable( $callback ) ) {
			return $this;
		}
		$this -> callback( $callback );
		if ( $option_name ) {
			self::$field['option_name'] = $option_name;
		}
		if ( $field_title && is_string( $field_title ) ) {
			self::$field['title'] = $field_title;
		}
		return $this;
	}

	/**
	 * Drowing page html
	 * 
	 * @return (void)
	 */
	public function page_body() {
		$menu_slug = $_GET['page'];
		$h2 = esc_html( $this -> page['page_title'] );
		$optionGroup = 'group_' . $menu_slug;
		?>
<div class="wrap">
  <h2><?= $h2 ?></h2>
  <?php if ( array_key_exists( 'description', $this -> page ) ) { ?>
  <?= $this -> page['description'] ?>
  <?php } ?>
  <form method="post" action="options.php">
    <?php settings_fields( $optionGroup ); ?>
    <?php do_settings_sections( $menu_slug ); ?>
    <?php submit_button(); ?>
  </form>
</div>
		<?php
	}

	/**
	 * @param  array $array
	 */
	public function section_body( $array ) {
		$args = $this -> callback_args[$array['id']];
		if ( array_key_exists( 'description', $args ) ) {
			?>
      <?= $args['description'] ?>
			<?php
		}
	}

	/**
	 *
	 */
	public function checkbox( $array ) {
		var_dump( $array );
		/*
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
		*/
	}

	public function test_field( $array ) {
		?>
        <pre>
<?php var_dump( $array ); ?>
        </pre>
		<?php
	}

}
