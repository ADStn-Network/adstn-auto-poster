<?php
/**
 * Plugin Name:       ADStn Auto Poster
 * Plugin URI:        https://github.com/ADStn-Network/adstn-auto-poster
 * Description:       Auto-publish and synchronize WordPress articles to the ADStn social platform seamlessly via Developer REST API & OAuth 2.0.
 * Version:           1.0.0
 * Author:            ADStn Developer Team
 * Author URI:        https://www.adstn.ovh
 * Text Domain:       adstn-auto-poster
 * Domain Path:       /languages
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package ADStn_Auto_Poster
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ADSTN_VERSION', '1.0.0' );
define( 'ADSTN_PLUGIN_FILE', __FILE__ );
define( 'ADSTN_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'ADSTN_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Require Includes
require_once ADSTN_PLUGIN_DIR . 'includes/class-adstn-activator.php';
require_once ADSTN_PLUGIN_DIR . 'includes/class-adstn-logger.php';
require_once ADSTN_PLUGIN_DIR . 'includes/class-adstn-api-client.php';
require_once ADSTN_PLUGIN_DIR . 'includes/class-adstn-publisher.php';
require_once ADSTN_PLUGIN_DIR . 'includes/class-adstn-metabox.php';
require_once ADSTN_PLUGIN_DIR . 'includes/class-adstn-admin.php';

/**
 * Activation & Deactivation Hooks.
 */
register_activation_hook( __FILE__, array( 'ADStn_Activator', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'ADStn_Activator', 'deactivate' ) );

/**
 * Initialize Plugin Core.
 */
function adstn_auto_poster_init() {

	// Initialize Publisher (Hooks into post transitions)
	new ADStn_Publisher();

	// Initialize Metabox in Post Editor
	if ( is_admin() ) {
		new ADStn_Metabox();
		new ADStn_Admin();
	}
}
add_action( 'plugins_loaded', 'adstn_auto_poster_init' );

/**
 * Add Settings Link to Plugins Table.
 *
 * @param array $links
 * @return array
 */
function adstn_auto_poster_action_links( $links ) {
	$settings_link = '<a href="' . esc_url( admin_url( 'admin.php?page=adstn-auto-poster' ) ) . '" style="font-weight:700;color:#615dfa;">' . esc_html__( 'Dashboard', 'adstn-auto-poster' ) . '</a>';
	array_unshift( $links, $settings_link );
	return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'adstn_auto_poster_action_links' );
