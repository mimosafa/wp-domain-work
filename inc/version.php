<?php

// Required PHP version
define( 'PHP_VER_WPDW_REQUIRE', '5.4.0' );

// Required WordPress version
define( 'WP_VER_WPDW_REQUIRE', '4.0' );

/**
 * Check PHP & WordPress version
 *
 * @return bool
 */
function requirement_wp_domain_work_plugin() {
	$e = new WP_Error();
	$phpVer = PHP_VERSION;
	$wpVer  = $GLOBALS['wp_version'];
	if ( version_compare( $phpVer, PHP_VER_WPDW_REQUIRE, '<' ) ) {
		$e->add(
			'error',
			sprintf(
				__( 'PHP version %s does not meet the requirements to use WP Domain Work plugin. %s or higher will be required.' ),
				$phpVer, PHP_VER_WPDW_REQUIRE
			)
		);
	}
	if ( version_compare( $wpVer, WP_VER_WPDW_REQUIRE, '<' ) ) {
		$e->add(
			'error',
			sprintf(
				__( 'WordPress version %s does not meet the requirements to use WP Domain Work plugin. %s or higher will be required.' ),
				esc_html( $wpVer ), WP_VER_WPDW_REQUIRE
			)
		);
	}
	if ( $e->get_error_code() ) {
		$GLOBALS['_wp_domain_work_error_messages'] = $e->get_error_messages();
		add_action( 'admin_notices', 'error_requirement_wp_domain_work_plugin' );
		return false;
	}
	return true;
}

/**
 * Print error message
 *
 * @return (void)
 */
function error_requirement_wp_domain_work_plugin() {
	$msg  = '<p>' . sprintf( __( 'The plugin <code>%s</code> has been <strong>deactivated</strong> due to an error: %s' ), 'WP Domain Work', '' ) . '</p>';
	$msgs = array_map( 'messages_requirement_wp_domain_work_plugin', $GLOBALS['_wp_domain_work_error_messages'] );
	$msg .= implode( "\n", $msgs );
	deactivate_plugins( plugin_basename( WPDW_PLUGIN_FILE ), true );
	echo "<div class=\"message error\">\n\t{$msg}\n</div>\n";
}

/**
 * Messages array formatting - array_map Callback
 */
function messages_requirement_wp_domain_work_plugin( $str ) {
	return "<p>$str</p>";
}
