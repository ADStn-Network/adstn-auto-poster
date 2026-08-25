<?php
/**
 * Admin Controller for ADStn Auto Poster.
 *
 * @package    ADStn_Auto_Poster
 * @subpackage ADStn_Auto_Poster/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ADStn_Admin {

	/**
	 * Settings.
	 *
	 * @var array
	 */
	private $settings;

	/**
	 * API Client.
	 *
	 * @var ADStn_API_Client
	 */
	private $api_client;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->settings   = get_option( 'adstn_settings', array() );
		$this->api_client = new ADStn_API_Client( $this->settings );

		// Admin Menu
		add_action( 'admin_menu', array( $this, 'register_admin_menu' ) );

		// Enqueue Assets
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );

		// Handle OAuth Callback on admin_init
		add_action( 'admin_init', array( $this, 'handle_oauth_callback' ) );

		// AJAX Endpoints
		add_action( 'wp_ajax_adstn_save_settings', array( $this, 'ajax_save_settings' ) );
		add_action( 'wp_ajax_adstn_test_connection', array( $this, 'ajax_test_connection' ) );
		add_action( 'wp_ajax_adstn_disconnect_account', array( $this, 'ajax_disconnect_account' ) );
		add_action( 'wp_ajax_adstn_refresh_profile', array( $this, 'ajax_refresh_profile' ) );
		add_action( 'wp_ajax_adstn_instant_share', array( $this, 'ajax_instant_share' ) );
		add_action( 'wp_ajax_adstn_retry_log', array( $this, 'ajax_retry_log' ) );
		add_action( 'wp_ajax_adstn_clear_logs', array( $this, 'ajax_clear_logs' ) );
		add_action( 'wp_ajax_adstn_get_log_details', array( $this, 'ajax_get_log_details' ) );
		add_action( 'wp_ajax_adstn_preview_template', array( $this, 'ajax_preview_template' ) );
	}

	/**
	 * Register Top-Level Admin Menu.
	 */
	public function register_admin_menu() {
		$icon_svg = 'data:image/svg+xml;base64,' . base64_encode( '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#a7aaad"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 14.5v-9l6 4.5-6 4.5z"/></svg>' );

		add_menu_page(
			__( 'ADStn Auto Poster', 'adstn-auto-poster' ),
			__( 'ADStn Poster', 'adstn-auto-poster' ),
			'manage_options',
			'adstn-auto-poster',
			array( $this, 'render_admin_page' ),
			$icon_svg,
			65
		);

		add_submenu_page(
			'adstn-auto-poster',
			__( 'Dashboard & Analytics', 'adstn-auto-poster' ),
			__( 'Dashboard', 'adstn-auto-poster' ),
			'manage_options',
			'adstn-auto-poster',
			array( $this, 'render_admin_page' )
		);

		add_submenu_page(
			'adstn-auto-poster',
			__( 'Connection Settings', 'adstn-auto-poster' ),
			__( 'Connection', 'adstn-auto-poster' ),
			'manage_options',
			'adstn-auto-poster&tab=connection',
			array( $this, 'render_admin_page' )
		);

		add_submenu_page(
			'adstn-auto-poster',
			__( 'Publishing Rules', 'adstn-auto-poster' ),
			__( 'Publish Rules', 'adstn-auto-poster' ),
			'manage_options',
			'adstn-auto-poster&tab=rules',
			array( $this, 'render_admin_page' )
		);

		add_submenu_page(
			'adstn-auto-poster',
			__( 'Post Template', 'adstn-auto-poster' ),
			__( 'Template', 'adstn-auto-poster' ),
			'manage_options',
			'adstn-auto-poster&tab=template',
			array( $this, 'render_admin_page' )
		);

		add_submenu_page(
			'adstn-auto-poster',
			__( 'Activity Logs', 'adstn-auto-poster' ),
			__( 'Logs', 'adstn-auto-poster' ),
			'manage_options',
			'adstn-auto-poster&tab=logs',
			array( $this, 'render_admin_page' )
		);
	}

	/**
	 * Enqueue Admin Assets.
	 *
	 * @param string $hook
	 */
	public function enqueue_admin_assets( $hook ) {
		// Load on our plugin page and post editing screens
		$is_plugin_page = ( strpos( $hook, 'adstn-auto-poster' ) !== false );
		$is_post_page   = in_array( $hook, array( 'post.php', 'post-new.php' ), true );

		if ( ! $is_plugin_page && ! $is_post_page ) {
			return;
		}

		$plugin_url = plugin_dir_url( dirname( __FILE__ ) );

		// CSS
		wp_enqueue_style(
			'adstn-admin-css',
			$plugin_url . 'admin/assets/css/admin.css',
			array(),
			ADSTN_VERSION
		);

		// JS
		wp_enqueue_script(
			'adstn-admin-js',
			$plugin_url . 'admin/assets/js/admin.js',
			array( 'jquery' ),
			ADSTN_VERSION,
			true
		);

		wp_localize_script(
			'adstn-admin-js',
			'adstnAdmin',
			array(
				'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
				'nonce'     => wp_create_nonce( 'adstn_admin_nonce' ),
				'i18n'      => array(
					'confirmDisconnect' => __( 'Are you sure you want to disconnect your ADStn account?', 'adstn-auto-poster' ),
					'confirmClearLogs'  => __( 'Are you sure you want to clear all activity logs?', 'adstn-auto-poster' ),
					'saving'            => __( 'Saving settings...', 'adstn-auto-poster' ),
					'saved'             => __( 'Settings saved successfully!', 'adstn-auto-poster' ),
					'testing'           => __( 'Testing connection...', 'adstn-auto-poster' ),
					'connected'         => __( 'Connection verified successfully!', 'adstn-auto-poster' ),
					'sharing'           => __( 'Sharing to ADStn...', 'adstn-auto-poster' ),
					'shared'            => __( 'Published successfully to ADStn!', 'adstn-auto-poster' ),
					'failed'            => __( 'Action failed!', 'adstn-auto-poster' ),
					'copied'            => __( 'Copied to clipboard!', 'adstn-auto-poster' ),
					'article'           => __( 'Article', 'adstn-auto-poster' ),
					'date'              => __( 'Date', 'adstn-auto-poster' ),
					'status'            => __( 'Status', 'adstn-auto-poster' ),
					'error'             => __( 'Error', 'adstn-auto-poster' ),
					'requestPayload'    => __( 'Request Payload', 'adstn-auto-poster' ),
					'apiResponse'       => __( 'Server API Response', 'adstn-auto-poster' ),
					'loadFailed'        => __( 'Failed to load log details.', 'adstn-auto-poster' ),
				),
			)
		);
	}

	/**
	 * Handle OAuth 2.0 Callback.
	 */
	public function handle_oauth_callback() {
		if ( ! isset( $_GET['page'] ) || 'adstn-auto-poster' !== $_GET['page'] ) {
			return;
		}

		if ( ! isset( $_GET['action'] ) || 'adstn_oauth_callback' !== $_GET['action'] ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized access.', 'adstn-auto-poster' ) );
		}

		// Handle error parameter from ADStn
		if ( isset( $_GET['error'] ) ) {
			$error_desc = isset( $_GET['error_description'] ) ? sanitize_text_field( wp_unslash( $_GET['error_description'] ) ) : sanitize_text_field( wp_unslash( $_GET['error'] ) );
			set_transient( 'adstn_admin_notice', array( 'type' => 'error', 'message' => sprintf( __( 'ADStn authorization failed: %s', 'adstn-auto-poster' ), $error_desc ) ), 30 );
			wp_safe_redirect( admin_url( 'admin.php?page=adstn-auto-poster&tab=connection' ) );
			exit;
		}

		// Code received
		if ( isset( $_GET['code'] ) ) {
			$code = sanitize_text_field( wp_unslash( $_GET['code'] ) );

			$result = $this->api_client->exchange_code_for_token( $code );

			if ( is_wp_error( $result ) ) {
				set_transient( 'adstn_admin_notice', array( 'type' => 'error', 'message' => sprintf( __( 'Error exchanging token: %s', 'adstn-auto-poster' ), $result->get_error_message() ) ), 30 );
			} else {
				set_transient( 'adstn_admin_notice', array( 'type' => 'success', 'message' => __( '🎉 ADStn account connected successfully! Ready for auto-publishing.', 'adstn-auto-poster' ) ), 30 );
			}

			wp_safe_redirect( admin_url( 'admin.php?page=adstn-auto-poster&tab=overview' ) );
			exit;
		}
	}

	/**
	 * Render Main Admin Page.
	 */
	public function render_admin_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$current_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'overview';
		$valid_tabs  = array( 'overview', 'connection', 'rules', 'template', 'logs', 'help' );

		if ( ! in_array( $current_tab, $valid_tabs, true ) ) {
			$current_tab = 'overview';
		}

		$settings       = get_option( 'adstn_settings', array() );
		$api_client     = new ADStn_API_Client( $settings );
		$is_connected   = $api_client->is_connected();
		$connected_user = isset( $settings['connected_user'] ) ? $settings['connected_user'] : array();
		$stats          = ADStn_Logger::get_stats();

		// Check for flash notice
		$flash_notice = get_transient( 'adstn_admin_notice' );
		if ( false !== $flash_notice ) {
			delete_transient( 'adstn_admin_notice' );
		}

		include plugin_dir_path( dirname( __FILE__ ) ) . 'admin/views/admin-dashboard.php';
	}

	/**
	 * AJAX: Save Settings.
	 */
	public function ajax_save_settings() {
		check_ajax_referer( 'adstn_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'adstn-auto-poster' ) ) );
		}

		$settings = get_option( 'adstn_settings', array() );

		// General & Connection Settings
		if ( isset( $_POST['enabled'] ) ) {
			$settings['enabled'] = '1' === sanitize_text_field( wp_unslash( $_POST['enabled'] ) ) ? '1' : '0';
		}

		if ( isset( $_POST['auth_method'] ) ) {
			$settings['auth_method'] = in_array( $_POST['auth_method'], array( 'oauth', 'manual_token' ), true ) ? sanitize_text_field( wp_unslash( $_POST['auth_method'] ) ) : 'oauth';
		}

		if ( isset( $_POST['client_id'] ) ) {
			$settings['client_id'] = sanitize_text_field( wp_unslash( $_POST['client_id'] ) );
		}

		if ( isset( $_POST['client_secret'] ) ) {
			$settings['client_secret'] = sanitize_text_field( wp_unslash( $_POST['client_secret'] ) );
		}

		if ( isset( $_POST['access_token'] ) ) {
			$new_token = sanitize_text_field( wp_unslash( $_POST['access_token'] ) );
			$settings['access_token'] = $new_token;
		}

		// Publishing Rules
		if ( isset( $_POST['post_types'] ) && is_array( $_POST['post_types'] ) ) {
			$settings['post_types'] = array_map( 'sanitize_text_field', wp_unslash( $_POST['post_types'] ) );
		} else {
			$settings['post_types'] = array( 'post' );
		}

		if ( isset( $_POST['post_events'] ) && is_array( $_POST['post_events'] ) ) {
			$settings['post_events'] = array_map( 'sanitize_text_field', wp_unslash( $_POST['post_events'] ) );
		} else {
			$settings['post_events'] = array( 'publish' );
		}

		if ( isset( $_POST['include_categories'] ) && is_array( $_POST['include_categories'] ) ) {
			$settings['include_categories'] = array_map( 'intval', wp_unslash( $_POST['include_categories'] ) );
		} else {
			$settings['include_categories'] = array();
		}

		if ( isset( $_POST['exclude_categories'] ) && is_array( $_POST['exclude_categories'] ) ) {
			$settings['exclude_categories'] = array_map( 'intval', wp_unslash( $_POST['exclude_categories'] ) );
		} else {
			$settings['exclude_categories'] = array();
		}

		if ( isset( $_POST['privacy'] ) ) {
			$privacy = sanitize_text_field( wp_unslash( $_POST['privacy'] ) );
			$settings['privacy'] = in_array( $privacy, array( 'public', 'followers', 'private' ), true ) ? $privacy : 'public';
		}

		// Template Settings
		if ( isset( $_POST['message_template'] ) ) {
			$settings['message_template'] = sanitize_textarea_field( wp_unslash( $_POST['message_template'] ) );
		}

		if ( isset( $_POST['excerpt_length'] ) ) {
			$settings['excerpt_length'] = max( 20, min( 1000, (int) $_POST['excerpt_length'] ) );
		}

		if ( isset( $_POST['hashtags_mode'] ) ) {
			$settings['hashtags_mode'] = sanitize_text_field( wp_unslash( $_POST['hashtags_mode'] ) );
		}

		if ( isset( $_POST['max_hashtags'] ) ) {
			$settings['max_hashtags'] = max( 0, min( 30, (int) $_POST['max_hashtags'] ) );
		}

		update_option( 'adstn_settings', $settings );

		// If manual token was supplied, try fetching user info
		if ( ! empty( $settings['access_token'] ) ) {
			$client = new ADStn_API_Client( $settings );
			$profile = $client->fetch_user_profile( true );
		}

		wp_send_json_success( array(
			'message'  => __( 'Settings saved successfully.', 'adstn-auto-poster' ),
			'settings' => $settings,
		) );
	}

	/**
	 * AJAX: Test Connection.
	 */
	public function ajax_test_connection() {
		check_ajax_referer( 'adstn_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'adstn-auto-poster' ) ) );
		}

		$settings = get_option( 'adstn_settings', array() );
		$client   = new ADStn_API_Client( $settings );
		$result   = $client->test_connection();

		if ( $result['connected'] ) {
			wp_send_json_success( $result );
		} else {
			wp_send_json_error( $result );
		}
	}

	/**
	 * AJAX: Disconnect Account.
	 */
	public function ajax_disconnect_account() {
		check_ajax_referer( 'adstn_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'adstn-auto-poster' ) ) );
		}

		$settings                     = get_option( 'adstn_settings', array() );
		$settings['access_token']     = '';
		$settings['refresh_token']    = '';
		$settings['token_expires_at'] = 0;
		$settings['connected_user']   = array();

		update_option( 'adstn_settings', $settings );
		delete_transient( 'adstn_user_profile' );

		wp_send_json_success( array( 'message' => __( 'Account disconnected successfully.', 'adstn-auto-poster' ) ) );
	}

	/**
	 * AJAX: Refresh User Profile.
	 */
	public function ajax_refresh_profile() {
		check_ajax_referer( 'adstn_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'adstn-auto-poster' ) ) );
		}

		$settings = get_option( 'adstn_settings', array() );
		$client   = new ADStn_API_Client( $settings );
		$profile  = $client->fetch_user_profile( true );

		if ( is_wp_error( $profile ) ) {
			wp_send_json_error( array( 'message' => $profile->get_error_message() ) );
		}

		wp_send_json_success( array(
			'message' => __( 'Profile details updated successfully.', 'adstn-auto-poster' ),
			'user'    => $profile,
		) );
	}

	/**
	 * AJAX: Instant Post Share (Metabox).
	 */
	public function ajax_instant_share() {
		check_ajax_referer( 'adstn_admin_nonce', 'nonce' );

		$post_id = isset( $_POST['post_id'] ) ? (int) $_POST['post_id'] : 0;
		if ( ! $post_id || ! current_user_can( 'edit_post', $post_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid post or unauthorized access.', 'adstn-auto-poster' ) ) );
		}

		$publisher = new ADStn_Publisher();
		$result    = $publisher->publish_post_to_adstn( $post_id );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}

		wp_send_json_success( array(
			'message' => __( '🎉 Article published successfully on ADStn platform!', 'adstn-auto-poster' ),
			'data'    => $result,
		) );
	}

	/**
	 * AJAX: Retry Failed Log.
	 */
	public function ajax_retry_log() {
		check_ajax_referer( 'adstn_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'adstn-auto-poster' ) ) );
		}

		$log_id = isset( $_POST['log_id'] ) ? (int) $_POST['log_id'] : 0;
		$log    = ADStn_Logger::get_log( $log_id );

		if ( ! $log || empty( $log['post_id'] ) ) {
			wp_send_json_error( array( 'message' => __( 'Log record not found.', 'adstn-auto-poster' ) ) );
		}

		$publisher = new ADStn_Publisher();
		$result    = $publisher->publish_post_to_adstn( $log['post_id'] );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}

		wp_send_json_success( array(
			'message' => __( 'Republished successfully!', 'adstn-auto-poster' ),
		) );
	}

	/**
	 * AJAX: Clear Logs.
	 */
	public function ajax_clear_logs() {
		check_ajax_referer( 'adstn_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'adstn-auto-poster' ) ) );
		}

		ADStn_Logger::clear_logs();
		wp_send_json_success( array( 'message' => __( 'Logs cleared successfully.', 'adstn-auto-poster' ) ) );
	}

	/**
	 * AJAX: Get Log Details (Modal).
	 */
	public function ajax_get_log_details() {
		check_ajax_referer( 'adstn_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'adstn-auto-poster' ) ) );
		}

		$log_id = isset( $_POST['log_id'] ) ? (int) $_POST['log_id'] : 0;
		$log    = ADStn_Logger::get_log( $log_id );

		if ( ! $log ) {
			wp_send_json_error( array( 'message' => __( 'Log record not found.', 'adstn-auto-poster' ) ) );
		}

		wp_send_json_success( $log );
	}

	/**
	 * AJAX: Preview Template Live.
	 */
	public function ajax_preview_template() {
		check_ajax_referer( 'adstn_admin_nonce', 'nonce' );

		$template = isset( $_POST['template'] ) ? sanitize_textarea_field( wp_unslash( $_POST['template'] ) ) : '';
		$preview  = ADStn_Publisher::generate_sample_preview( $template );

		wp_send_json_success( array( 'preview' => $preview ) );
	}
}
