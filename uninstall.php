<?php

if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

require_once dirname( __FILE__ ) . '/class/wp-domain-work.php';
$options = WP_Domain_Work::getOptKey();
foreach ( $options as $option ) {
	delete_option( $option );
}
