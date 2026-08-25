<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package ADStn_Auto_Poster
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

// Delete plugin options
delete_option( 'adstn_settings' );
delete_option( 'adstn_activity_logs' );
delete_transient( 'adstn_user_profile' );
delete_transient( 'adstn_admin_notice' );

// Clean custom post meta created by the plugin
$wpdb->query( "DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE '_adstn_%'" );
