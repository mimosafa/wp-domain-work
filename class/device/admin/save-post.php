<?php
namespace WPDW\Device\Admin;

use WPDW\Options as OPT;

class save_post {

	private $post_type;

	/**
	 * @var WP_Domain\{$domain}\property
	 */
	private $property;

	/**
	 * @var WPDW\WP\nonce
	 */
	private $nonce;

	/**
	 * Constructor
	 *
	 * @access protected
	 * @param  string $domain
	 */
	public function __construct( $domain ) {
		if ( ! $domain = filter_var( $domain ) )
			return;
		$class = 'WP_Domain\\' . $domain . '\\property';
		if ( ! class_exists( $class ) )
			return;
		$this->property = $class::getInstance();
		$this->nonce = new \WPDW\WP\nonce( $domain );
		$this->init();
	}

	public function init() {
		add_action( 'save_post', [ &$this, 'save_post' ] );
	}

	/**
	 * @access public
	 */
	public function save_post( $post_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && \DOING_AUTOSAVE )
			return $post_id;
		if ( ! $_POST )
			return $post_id;
		if ( ! $settings = $this->property->get_setting() )
			return $post_id;

		foreach ( $settings as $key => $arg ) {
			$nonce = $this->nonce->get_nonce( $key );
			if ( ! array_key_exists( $nonce, $_POST ) )
				continue;
			if ( ! $this->nonce->check_admin_referer( $key ) )
				continue;
			if ( ! array_key_exists( $key, $_POST ) )
				continue;
			if ( ! $assetInstance = $this->property->$key )
				continue;
			if ( is_array( $_POST[$key] ) )
				$value = filter_input( \INPUT_POST, $key, \FILTER_DEFAULT, \FILTER_FORCE_ARRAY );
			else
				$value = filter_input( \INPUT_POST, $key );
			$assetInstance->update( $post_id, $value );
		}

	}

}
