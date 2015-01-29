<?php

if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

$options = $GLOBALS['wp_domain_work_plugin_option_keys'];
foreach ( $options as $option ) {
	delete_option( $option );
}
