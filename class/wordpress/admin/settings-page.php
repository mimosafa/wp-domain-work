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
	private $pages = [];

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
	 *
	 */
	public function page( $menu_slug, $page_title = '', $menu_title = '' ) {
		//
	}

	/**
	 *
	 */
	public function init( $arg = [] ) {
		if ( $arg && is_array( $arg ) && !\utility\is_vector( $arg ) ) {
			$this -> pages = $arg;
		}
		if ( $this -> pages ) {
			add_action( 'admin_menu', [ $this, 'add_pages' ] );
			add_action( 'admin_init', [ $this, 'add_settings' ] );
		}
	}

	/**
	 * 
	 */
	public function add_pages() {

		global $menu, $admin_page_hooks, $_registered_pages, $_parent_pages;

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
				$page_title = ucfirst( trim( str_replace( [ '-', '_', '/', '.php' ], ' ', $menu_slug ) ) );
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
