<?php
/**
 * API Client for interacting with the ADStn Developer Platform.
 *
 * @package    ADStn_Auto_Poster
 * @subpackage ADStn_Auto_Poster/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ADStn_API_Client {

	/**
	 * Base platform URL.
	 */
	const BASE_URL = 'https://www.adstn.ovh';

	/**
	 * OAuth Authorize Endpoint.
	 */
	const AUTHORIZE_ENDPOINT = '/oauth/authorize';

	/**
	 * OAuth Token Endpoint.
	 */
	const TOKEN_ENDPOINT = '/oauth/token';

	/**
	 * User Profile Endpoint.
	 */
	const PROFILE_ENDPOINT = '/api/developer/v1/me/profile';

	/**
	 * User Identity Endpoint.
	 */
	const ME_ENDPOINT = '/api/developer/v1/me';

	/**
	 * Content Publish Endpoint.
	 */
	const CONTENT_ENDPOINT = '/api/developer/v1/me/content';

	/**
	 * OAuth Scopes needed for auto-poster.
	 */
	const REQUIRED_SCOPES = 'user.identity.read user.content.write';

	/**
	 * Client ID.
	 *
	 * @var string
	 */
	private $client_id = '';

	/**
	 * Client Secret.
	 *
	 * @var string
	 */
	private $client_secret = '';

	/**
	 * Access Token.
	 *
	 * @var string
	 */
	private $access_token = '';

	/**
	 * Refresh Token.
	 *
	 * @var string
	 */
	private $refresh_token = '';

	/**
	 * Token expiration timestamp.
	 *
	 * @var int
	 */
	private $token_expires_at = 0;

	/**
	 * Constructor.
	 *
	 * @param array|null $settings Optional custom settings array.
	 */
	public function __construct( $settings = null ) {
		if ( null === $settings ) {
			$settings = get_option( 'adstn_settings', array() );
		}

		$this->client_id        = isset( $settings['client_id'] ) ? trim( $settings['client_id'] ) : '';
		$this->client_secret    = isset( $settings['client_secret'] ) ? trim( $settings['client_secret'] ) : '';
		$this->access_token     = isset( $settings['access_token'] ) ? trim( $settings['access_token'] ) : '';
		$this->refresh_token    = isset( $settings['refresh_token'] ) ? trim( $settings['refresh_token'] ) : '';
		$this->token_expires_at = isset( $settings['token_expires_at'] ) ? (int) $settings['token_expires_at'] : 0;
	}

	/**
	 * Get OAuth Callback (Redirect) URL.
	 *
	 * @return string
	 */
	public static function get_redirect_uri() {
		return admin_url( 'admin.php?page=adstn-auto-poster&action=adstn_oauth_callback' );
	}

	/**
	 * Build OAuth 2.0 Authorization URL.
	 *
	 * @param string $state Random CSRF state token.
	 * @return string
	 */
	public function get_authorization_url( $state = '' ) {
		if ( empty( $state ) ) {
			$state = wp_create_nonce( 'adstn_oauth_state' );
		}

		$params = array(
			'client_id'     => $this->client_id,
			'redirect_uri'  => self::get_redirect_uri(),
			'response_type' => 'code',
			'scope'         => self::REQUIRED_SCOPES,
			'state'         => $state,
		);

		return self::BASE_URL . self::AUTHORIZE_ENDPOINT . '?' . http_build_query( $params );
	}

	/**
	 * Exchange Authorization Code for Access & Refresh Tokens.
	 *
	 * @param string $code Authorization code.
	 * @return array|WP_Error
	 */
	public function exchange_code_for_token( $code ) {
		if ( empty( $this->client_id ) || empty( $this->client_secret ) ) {
			return new WP_Error( 'missing_credentials', __( 'Client ID or Client Secret is missing.', 'adstn-auto-poster' ) );
		}

		$payload = array(
			'grant_type'    => 'authorization_code',
			'client_id'     => $this->client_id,
			'client_secret' => $this->client_secret,
			'redirect_uri'  => self::get_redirect_uri(),
			'code'          => $code,
		);

		$response = wp_remote_post(
			self::BASE_URL . self::TOKEN_ENDPOINT,
			array(
				'headers'   => array(
					'Accept'       => 'application/json',
					'Content-Type' => 'application/x-www-form-urlencoded',
				),
				'body'      => $payload,
				'timeout'   => 25,
				'sslverify' => true,
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		$body        = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( $status_code >= 400 || empty( $body['access_token'] ) ) {
			$err_msg = isset( $body['message'] ) ? $body['message'] : ( isset( $body['error'] ) ? $body['error'] : __( 'Token exchange failed.', 'adstn-auto-poster' ) );
			return new WP_Error( 'token_exchange_error', $err_msg, $body );
		}

		$expires_in = isset( $body['expires_in'] ) ? (int) $body['expires_in'] : 3600;
		$expires_at = time() + $expires_in;

		// Save tokens to settings
		$settings                     = get_option( 'adstn_settings', array() );
		$settings['access_token']     = $body['access_token'];
		$settings['refresh_token']    = isset( $body['refresh_token'] ) ? $body['refresh_token'] : $this->refresh_token;
		$settings['token_expires_at'] = $expires_at;

		$this->access_token     = $settings['access_token'];
		$this->refresh_token    = $settings['refresh_token'];
		$this->token_expires_at = $expires_at;

		// Fetch profile immediately and cache
		$profile_result = $this->fetch_user_profile();
		if ( ! is_wp_error( $profile_result ) && ! empty( $profile_result ) ) {
			$settings['connected_user'] = $profile_result;
		}

		update_option( 'adstn_settings', $settings );

		return $body;
	}

	/**
	 * Refresh access token using refresh_token.
	 *
	 * @return bool|WP_Error
	 */
	public function refresh_access_token() {
		if ( empty( $this->refresh_token ) || empty( $this->client_id ) || empty( $this->client_secret ) ) {
			return new WP_Error( 'no_refresh_token', __( 'Cannot refresh token: missing refresh token or client credentials.', 'adstn-auto-poster' ) );
		}

		$payload = array(
			'grant_type'    => 'refresh_token',
			'client_id'     => $this->client_id,
			'client_secret' => $this->client_secret,
			'refresh_token' => $this->refresh_token,
		);

		$response = wp_remote_post(
			self::BASE_URL . self::TOKEN_ENDPOINT,
			array(
				'headers'   => array(
					'Accept'       => 'application/json',
					'Content-Type' => 'application/x-www-form-urlencoded',
				),
				'body'      => $payload,
				'timeout'   => 25,
				'sslverify' => true,
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		$body        = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( $status_code >= 400 || empty( $body['access_token'] ) ) {
			$err_msg = isset( $body['message'] ) ? $body['message'] : __( 'Token refresh failed.', 'adstn-auto-poster' );
			return new WP_Error( 'token_refresh_failed', $err_msg, $body );
		}

		$expires_in = isset( $body['expires_in'] ) ? (int) $body['expires_in'] : 3600;
		$expires_at = time() + $expires_in;

		$settings                     = get_option( 'adstn_settings', array() );
		$settings['access_token']     = $body['access_token'];
		if ( ! empty( $body['refresh_token'] ) ) {
			$settings['refresh_token'] = $body['refresh_token'];
			$this->refresh_token       = $body['refresh_token'];
		}
		$settings['token_expires_at'] = $expires_at;

		$this->access_token     = $settings['access_token'];
		$this->token_expires_at = $expires_at;

		update_option( 'adstn_settings', $settings );

		return true;
	}

	/**
	 * Get a valid access token, auto-refreshing if expired.
	 *
	 * @return string|WP_Error
	 */
	public function get_valid_access_token() {
		if ( empty( $this->access_token ) ) {
			return new WP_Error( 'no_token', __( 'No Access Token found. Please connect your ADStn account.', 'adstn-auto-poster' ) );
		}

		// If token expires within 2 minutes and refresh token exists, refresh it
		if ( $this->token_expires_at > 0 && ( $this->token_expires_at - time() ) < 120 ) {
			if ( ! empty( $this->refresh_token ) ) {
				$refresh_res = $this->refresh_access_token();
				if ( is_wp_error( $refresh_res ) ) {
					// Fallback to current token anyway in case server clock differs
				}
			}
		}

		return $this->access_token;
	}

	/**
	 * Fetch Authenticated User Profile info from ADStn.
	 *
	 * @param bool $force_refresh Force API call bypassing cache.
	 * @return array|WP_Error
	 */
	public function fetch_user_profile( $force_refresh = false ) {
		if ( ! $force_refresh ) {
			$cached = get_transient( 'adstn_user_profile' );
			if ( false !== $cached && is_array( $cached ) ) {
				return $cached;
			}
		}

		$token = $this->get_valid_access_token();
		if ( is_wp_error( $token ) ) {
			return $token;
		}

		// Try Profile endpoint first
		$response = wp_remote_get(
			self::BASE_URL . self::PROFILE_ENDPOINT,
			array(
				'headers'   => array(
					'Authorization'   => 'Bearer ' . $token,
					'Accept'          => 'application/json',
					'Accept-Language' => get_locale(),
				),
				'timeout'   => 20,
				'sslverify' => true,
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		$body        = json_decode( wp_remote_retrieve_body( $response ), true );

		// If profile endpoint returns 404 or fails, try /api/developer/v1/me
		if ( $status_code >= 400 || empty( $body ) ) {
			$response_me = wp_remote_get(
				self::BASE_URL . self::ME_ENDPOINT,
				array(
					'headers'   => array(
						'Authorization'   => 'Bearer ' . $token,
						'Accept'          => 'application/json',
						'Accept-Language' => get_locale(),
					),
					'timeout'   => 20,
					'sslverify' => true,
				)
			);

			if ( ! is_wp_error( $response_me ) ) {
				$status_code = wp_remote_retrieve_response_code( $response_me );
				$body        = json_decode( wp_remote_retrieve_body( $response_me ), true );
			}
		}

		if ( $status_code >= 400 ) {
			/* translators: %d: HTTP status code from the API response */
			$err = isset( $body['message'] ) ? $body['message'] : sprintf( __( 'API returned HTTP %d', 'adstn-auto-poster' ), $status_code );
			return new WP_Error( 'profile_fetch_error', $err, $body );
		}

		$user_data = array();
		if ( isset( $body['data'] ) && is_array( $body['data'] ) ) {
			$user_data = $body['data'];
		} elseif ( is_array( $body ) ) {
			$user_data = $body;
		}

		// Normalize data structure for UI display
		$normalized = array(
			'id'              => isset( $user_data['id'] ) ? $user_data['id'] : ( isset( $user_data['user_id'] ) ? $user_data['user_id'] : '' ),
			'username'        => isset( $user_data['username'] ) ? $user_data['username'] : ( isset( $user_data['user_name'] ) ? $user_data['user_name'] : '' ),
			'name'            => isset( $user_data['name'] ) ? $user_data['name'] : ( isset( $user_data['full_name'] ) ? $user_data['full_name'] : ( isset( $user_data['user_fullname'] ) ? $user_data['user_fullname'] : '' ) ),
			'avatar'          => isset( $user_data['avatar'] ) ? $user_data['avatar'] : ( isset( $user_data['avatar_url'] ) ? $user_data['avatar_url'] : ( isset( $user_data['user_picture'] ) ? $user_data['user_picture'] : '' ) ),
			'cover'           => isset( $user_data['cover'] ) ? $user_data['cover'] : ( isset( $user_data['user_cover'] ) ? $user_data['user_cover'] : '' ),
			'email'           => isset( $user_data['email'] ) ? $user_data['email'] : ( isset( $user_data['user_email'] ) ? $user_data['user_email'] : '' ),
			'followers_count' => isset( $user_data['followers_count'] ) ? $user_data['followers_count'] : ( isset( $user_data['followers'] ) ? $user_data['followers'] : 0 ),
			'verified'        => ! empty( $user_data['verified'] ) || ! empty( $user_data['user_verified'] ),
			'fetched_at'      => current_time( 'mysql' ),
		);

		// Cache for 10 minutes
		set_transient( 'adstn_user_profile', $normalized, 10 * MINUTE_IN_SECONDS );

		// Update stored settings
		$settings                   = get_option( 'adstn_settings', array() );
		$settings['connected_user'] = $normalized;
		update_option( 'adstn_settings', $settings );

		return $normalized;
	}

	/**
	 * Publish a Post / Content to ADStn.
	 *
	 * @param string $content Post text (can include URLs, tags, etc.).
	 * @param string $privacy 'public', 'followers', or 'private'.
	 * @return array|WP_Error
	 */
	public function publish_content( $content, $privacy = 'public' ) {
		$token = $this->get_valid_access_token();
		if ( is_wp_error( $token ) ) {
			return $token;
		}

		if ( empty( trim( $content ) ) ) {
			return new WP_Error( 'empty_content', __( 'Post content cannot be empty.', 'adstn-auto-poster' ) );
		}

		$allowed_privacy = array( 'public', 'followers', 'private' );
		if ( ! in_array( $privacy, $allowed_privacy, true ) ) {
			$privacy = 'public';
		}

		$payload = array(
			'content' => $content,
			'privacy' => $privacy,
		);

		$response = wp_remote_post(
			self::BASE_URL . self::CONTENT_ENDPOINT,
			array(
				'headers'   => array(
					'Authorization'   => 'Bearer ' . $token,
					'Content-Type'    => 'application/json',
					'Accept'          => 'application/json',
					'Accept-Language' => get_locale(),
				),
				'body'      => wp_json_encode( $payload ),
				'timeout'   => 30,
				'sslverify' => true,
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		$raw_body    = wp_remote_retrieve_body( $response );
		$body        = json_decode( $raw_body, true );

		if ( $status_code === 429 ) {
			return new WP_Error( 'rate_limit_exceeded', __( 'ADStn API rate limit reached (30 requests/minute). Please retry in a few moments.', 'adstn-auto-poster' ), $body );
		}

		if ( $status_code >= 400 || ( isset( $body['success'] ) && false === $body['success'] ) ) {
			/* translators: %d: HTTP status code from the ADStn API response */
			$err_msg = isset( $body['message'] ) ? $body['message'] : ( isset( $body['error'] ) ? $body['error'] : sprintf( __( 'ADStn API Error (HTTP %d)', 'adstn-auto-poster' ), $status_code ) );
			return new WP_Error( 'publish_error', $err_msg, array(
				'http_code' => $status_code,
				'response'  => $body ? $body : $raw_body,
				'payload'   => $payload,
			) );
		}

		return array(
			'success'   => true,
			'http_code' => $status_code,
			'data'      => isset( $body['data'] ) ? $body['data'] : $body,
			'message'   => isset( $body['message'] ) ? $body['message'] : __( 'Published successfully to ADStn!', 'adstn-auto-poster' ),
			'raw'       => $body,
		);
	}

	/**
	 * Test Current Connection.
	 *
	 * @return array
	 */
	public function test_connection() {
		if ( empty( $this->access_token ) ) {
			return array(
				'connected' => false,
				'message'   => __( 'No access token configured. Please connect your account first.', 'adstn-auto-poster' ),
			);
		}

		$profile = $this->fetch_user_profile( true );
		if ( is_wp_error( $profile ) ) {
			return array(
				'connected' => false,
				'message'   => $profile->get_error_message(),
				'error'     => $profile,
			);
		}

		return array(
			'connected' => true,
			'message'   => __( 'Connection verified successfully! Account is active.', 'adstn-auto-poster' ),
			'user'      => $profile,
		);
	}

	/**
	 * Check if account is connected.
	 *
	 * @return bool
	 */
	public function is_connected() {
		return ! empty( $this->access_token );
	}
}
