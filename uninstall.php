<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package ADStn_Auto_Poster
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Delete plugin options
delete_option( 'adstn_settings' );
delete_option( 'adstn_activity_logs' );
delete_transient( 'adstn_user_profile' );
delete_transient( 'adstn_admin_notice' );

// Clean custom post meta created by the plugin.
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Uninstall routine; bulk cleanup of plugin-specific meta is acceptable here.
global $wpdb;
$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE %s", '_adstn_%' ) );
