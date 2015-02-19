<?php

if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

require_once dirname( __FILE__ ) . '/inc/version.php';
if ( requirement_wp_domain_work_plugin( false ) ) {
	$options = $GLOBALS['wp_domain_work_plugin_option_keys'];
	foreach ( $options as $option ) {
		delete_option( $option );
	}
}
