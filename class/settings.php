<?php
namespace WPDW;

class Settings {
	use Util\Singleton { getInstance as init; }

	/**
	 * @var WPDW\WP\settings_page
	 */
	private $pageInstance;
	
	/**
	 * Constructor
	 */
	protected function __construct() {
		$this->pageInstance = new WP\settings_page();
		$this->pages();
		$this->pageInstance->done();
	}

	private function pages() {
		if ( ! Options::get_domains_all() ) {
			$this->pageInstance->init( 'wp-domain-work-settings', 'WP Domain Work Settings', 'Domains' );
			$this->default_settings();
		} else {
			$this->pageInstance
				->init( 'wp-domain-work-domains', 'Your Domains', 'Domains' )
					->section( 'raw-data', 'Raw data' )
					->field( 'active-domains' )
						->html( '<pre>' . var_export(Options::get_domains(), true ) . '</pre>' )
					->field( 'all-domains' )
						->html( '<pre>' . var_export(Options::get_domains_all(), true ) . '</pre>' )
				->init( 'wp-domain-work-settings', 'WP Domain Work Settings', 'Settings' )
			;
			$this->default_settings();
		}
	}

	private function default_settings() {
		$this->pageInstance
			->description( __( 'Manage custom post type, custom taxonomy, and custom field by directory and file base.' ) )
			->section( 'domains-directories', 'Domains Directory' )
				->field( 'sample-domains-directory' )
					->option_name(
						Options::getKey( 'sample_domains' ),
						'checkbox',
						[ 'label' => '<code>wp-content/plugins/wp-domain-work/domains/</code>' ]
					)
				->field( 'root-domains-directory' )
					->option_name(
						Options::getKey( 'root_domains' ),
						'checkbox',
						[ 'label' => '<code>wp-content/domains</code>' ]
					)
				->field( 'theme-domains-directory' )
					->option_name(
						Options::getKey( 'theme_domains' ),
						'checkbox',
						[ 'label' => '<code>wp-content/themes/your-theme/domains</code>' ]
					)
					->description( 'If using child theme, child theme has priority.' )
		;
		if ( Options::get_root_domains() ?: Options::get_theme_domains() ?: Options::get_sample_domains() ?: false ) {
			$this->pageInstance
				->field( 'reflesh-domains', '<span style="color:#c7254e;">Reflesh Domains</span>' )
				->option_name(
					Options::getKey( 'force_dir_scan' ),
					'checkbox',
					[ 'label' => '<strong>Forcefully Refresh the Domains Directories.</strong>' ]
				)
			;
		}
	}

}
