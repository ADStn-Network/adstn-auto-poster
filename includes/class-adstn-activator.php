<?php
/**
 * Fired during plugin activation & deactivation.
 *
 * @package    ADStn_Auto_Poster
 * @subpackage ADStn_Auto_Poster/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ADStn_Activator {

	/**
	 * Run on plugin activation.
	 */
	public static function activate() {
		self::set_default_options();
	}

	/**
	 * Set default options if not exists.
	 */
	private static function set_default_options() {
		$default_settings = array(
			'enabled'            => '1',
			'client_id'          => '',
			'client_secret'      => '',
			'access_token'       => '',
			'refresh_token'      => '',
			'token_expires_at'   => 0,
			'auth_method'        => 'oauth', // 'oauth' or 'manual_token'
			'post_types'         => array( 'post' ),
			'post_events'        => array( 'publish' ),
			'include_categories' => array(), // empty means all
			'exclude_categories' => array(),
			'privacy'            => 'public', // 'public', 'followers', 'private'
			'message_template'   => "{title}\n\n{excerpt}\n\n{url}",
			'excerpt_length'     => 160,
			'include_tags'       => '1',
			'hashtags_mode'      => 'tags', // 'tags', 'categories', 'both', 'none'
			'max_hashtags'       => 5,
			'connected_user'     => array(),
		);

		$existing = get_option( 'adstn_settings' );
		if ( false === $existing ) {
			add_option( 'adstn_settings', $default_settings );
		} else {
			// Merge missing keys
			$merged = wp_parse_args( $existing, $default_settings );
			update_option( 'adstn_settings', $merged );
		}
	}

	/**
	 * Run on plugin deactivation.
	 */
	public static function deactivate() {
		// Clean transient data
		delete_transient( 'adstn_user_profile' );
		delete_transient( 'adstn_admin_notice' );
	}
}
