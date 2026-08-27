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

// Clean custom post meta created by the plugin using standard WordPress API.
$adstn_meta_keys = array(
	'_adstn_published',
	'_adstn_published_at',
	'_adstn_auto_publish',
	'_adstn_custom_message',
	'_adstn_last_error',
	'_adstn_error_time',
	'_adstn_adstn_post_id',
);

foreach ( $adstn_meta_keys as $adstn_meta_key ) {
	delete_post_meta_by_key( $adstn_meta_key );
}


