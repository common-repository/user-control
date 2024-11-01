<?php
// this is the uninstall handler
// include unregister_setting, delete_option, and other uninstall behavior here

//if uninstall not called from WordPress exit
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit ();

function dropStatus()
{
	global $wpdb;
	$wpdb->query("DROP TABLE ".$wpdb->prefix."usercontrol");
	$wpdb->query("DROP TABLE ".$wpdb->prefix."default_status");
}

dropStatus();

?>