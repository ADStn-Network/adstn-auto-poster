<?php
/**
 * Tab: Connection & API Settings.
 *
 * @package ADStn_Auto_Poster
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$adstn_redirect_uri = ADStn_API_Client::get_redirect_uri();
$adstn_auth_url     = $api_client->get_authorization_url();
$adstn_auth_method  = ! empty( $settings['auth_method'] ) ? $settings['auth_method'] : 'oauth';
$adstn_expires_at   = ! empty( $settings['token_expires_at'] ) ? (int) $settings['token_expires_at'] : 0;
?>

<form id="adstn-connection-form" class="adstn-form" method="post">

	<!-- Connection Method Card -->
	<div class="adstn-card">
		<div class="adstn-card-header">
			<h2 class="adstn-card-title">
				<span class="dashicons dashicons-shield"></span>
				<?php esc_html_e( 'Authentication Method', 'adstn-auto-poster' ); ?>
			</h2>
			<div class="adstn-segmented-control">
				<label class="adstn-segment-label <?php echo 'oauth' === $adstn_auth_method ? 'is-active' : ''; ?>">
					<input type="radio" name="auth_method" value="oauth" <?php checked( $adstn_auth_method, 'oauth' ); ?>>
					<span>OAuth 2.0 (<?php esc_html_e( 'Recommended', 'adstn-auto-poster' ); ?>)</span>
				</label>
				<label class="adstn-segment-label <?php echo 'manual_token' === $adstn_auth_method ? 'is-active' : ''; ?>">
					<input type="radio" name="auth_method" value="manual_token" <?php checked( $adstn_auth_method, 'manual_token' ); ?>>
					<span><?php esc_html_e( 'Manual Access Token', 'adstn-auto-poster' ); ?></span>
				</label>
			</div>
		</div>

		<div class="adstn-card-body">

			<!-- Step 1: Callback URI Helper -->
			<div class="adstn-alert adstn-alert-info">
				<div class="adstn-alert-icon">
					<span class="dashicons dashicons-admin-links"></span>
				</div>
				<div class="adstn-alert-content">
					<strong><?php esc_html_e( 'Authorized Redirect URI:', 'adstn-auto-poster' ); ?></strong>
					<p style="margin: 4px 0 8px;"><?php esc_html_e( 'Copy this URL and paste it into the Redirect URIs field when creating your app on ADStn Developer Platform:', 'adstn-auto-poster' ); ?></p>
					<div class="adstn-copy-box">
						<input type="text" readonly id="adstn-redirect-uri-input" class="adstn-input" value="<?php echo esc_url( $adstn_redirect_uri ); ?>">
						<button type="button" class="adstn-btn adstn-btn-secondary js-adstn-copy" data-target="#adstn-redirect-uri-input">
							<span class="dashicons dashicons-clipboard"></span>
							<span><?php esc_html_e( 'Copy URL', 'adstn-auto-poster' ); ?></span>
						</button>
					</div>
				</div>
			</div>

			<!-- OAuth 2.0 Credentials Section -->
			<div id="adstn-oauth-section" class="adstn-auth-section" style="<?php echo 'oauth' !== $adstn_auth_method ? 'display:none;' : ''; ?>">

				<div class="adstn-form-grid">
					<div class="adstn-form-group">
						<label for="adstn-client-id" class="adstn-label">
							<?php esc_html_e( 'Client ID', 'adstn-auto-poster' ); ?>
							<span class="adstn-req">*</span>
						</label>
						<input type="text" id="adstn-client-id" name="client_id" class="adstn-input" value="<?php echo esc_attr( ! empty( $settings['client_id'] ) ? $settings['client_id'] : '' ); ?>" placeholder="e.g. 32-character hexadecimal identifier">
						<span class="adstn-help-text"><?php esc_html_e( 'Generated upon creating your application in the ADStn Developer Platform.', 'adstn-auto-poster' ); ?></span>
					</div>

					<div class="adstn-form-group">
						<label for="adstn-client-secret" class="adstn-label">
							<?php esc_html_e( 'Client Secret', 'adstn-auto-poster' ); ?>
							<span class="adstn-req">*</span>
						</label>
						<div class="adstn-password-wrap">
							<input type="password" id="adstn-client-secret" name="client_secret" class="adstn-input" value="<?php echo esc_attr( ! empty( $settings['client_secret'] ) ? $settings['client_secret'] : '' ); ?>" placeholder="••••••••••••••••••••••••">
							<button type="button" class="adstn-btn-toggle-pwd" title="<?php esc_attr_e( 'Toggle Visibility', 'adstn-auto-poster' ); ?>">
								<span class="dashicons dashicons-visibility"></span>
							</button>
						</div>
						<span class="adstn-help-text"><?php esc_html_e( 'Keep this credential confidential and never share it publicly.', 'adstn-auto-poster' ); ?></span>
					</div>
				</div>

				<div class="adstn-oauth-connect-box">
					<?php if ( ! empty( $settings['client_id'] ) && ! empty( $settings['client_secret'] ) ) : ?>
						<a href="<?php echo esc_url( $adstn_auth_url ); ?>" class="adstn-btn adstn-btn-primary adstn-btn-lg">
							<span class="dashicons dashicons-admin-links"></span>
							<span><?php esc_html_e( 'Connect & Authorize with ADStn', 'adstn-auto-poster' ); ?> &rarr;</span>
						</a>
					<?php else : ?>
						<button type="button" class="adstn-btn adstn-btn-primary adstn-btn-lg" disabled title="<?php esc_attr_e( 'Please save Client ID and Client Secret first', 'adstn-auto-poster' ); ?>">
							<span class="dashicons dashicons-admin-links"></span>
							<span><?php esc_html_e( 'Save Credentials to Enable One-Click Connect', 'adstn-auto-poster' ); ?></span>
						</button>
					<?php endif; ?>

					<a href="https://www.adstn.ovh/developer/apps/create" target="_blank" class="adstn-btn adstn-btn-secondary adstn-btn-lg">
						<span class="dashicons dashicons-plus-alt"></span>
						<span><?php esc_html_e( 'Create New App on ADStn', 'adstn-auto-poster' ); ?></span>
					</a>
				</div>

			</div>

			<!-- Manual Token Section -->
			<div id="adstn-manual-token-section" class="adstn-auth-section" style="<?php echo 'manual_token' !== $adstn_auth_method ? 'display:none;' : ''; ?>">
				<div class="adstn-form-group">
					<label for="adstn-access-token" class="adstn-label">
						<?php esc_html_e( 'Bearer Access Token', 'adstn-auto-poster' ); ?>
						<span class="adstn-req">*</span>
					</label>
					<div class="adstn-password-wrap">
						<input type="password" id="adstn-access-token" name="access_token" class="adstn-input" value="<?php echo esc_attr( ! empty( $settings['access_token'] ) ? $settings['access_token'] : '' ); ?>" placeholder="e.g. def50200a87...">
						<button type="button" class="adstn-btn-toggle-pwd" title="<?php esc_attr_e( 'Toggle Visibility', 'adstn-auto-poster' ); ?>">
							<span class="dashicons dashicons-visibility"></span>
						</button>
					</div>
					<span class="adstn-help-text"><?php esc_html_e( 'If you generated an access token directly on ADStn, paste it here for instant connection.', 'adstn-auto-poster' ); ?></span>
				</div>
			</div>

		</div>
	</div>

	<!-- Token Status & Expiry Card -->
	<?php if ( $is_connected ) : ?>
		<div class="adstn-card" style="margin-top: 24px;">
			<div class="adstn-card-header">
				<h2 class="adstn-card-title">
					<span class="dashicons dashicons-lock"></span>
					<?php esc_html_e( 'Session & Token Details', 'adstn-auto-poster' ); ?>
				</h2>
				<button type="button" id="adstn-disconnect-btn-2" class="adstn-btn-sm adstn-btn-danger">
					<?php esc_html_e( 'Disconnect', 'adstn-auto-poster' ); ?>
				</button>
			</div>
			<div class="adstn-card-body">
				<div class="adstn-grid-3">
					<div class="adstn-info-tile">
						<span class="adstn-info-title"><?php esc_html_e( 'Auth Status', 'adstn-auto-poster' ); ?></span>
						<strong style="color: #10d876;"><?php esc_html_e( 'Connected & Valid', 'adstn-auto-poster' ); ?></strong>
					</div>
					<div class="adstn-info-tile">
						<span class="adstn-info-title"><?php esc_html_e( 'Token Expiry', 'adstn-auto-poster' ); ?></span>
						<strong>
							<?php
							if ( $adstn_expires_at > 0 ) {
								$adstn_remaining = $adstn_expires_at - time();
								if ( $adstn_remaining > 0 ) {
									/* translators: %d: number of minutes until token expires */
									echo esc_html( sprintf( __( 'Expires in %d minutes (auto-refreshes)', 'adstn-auto-poster' ), round( $adstn_remaining / 60 ) ) );
								} else {
									echo esc_html__( 'Expired (will auto-refresh on demand)', 'adstn-auto-poster' );
								}
							} else {
								echo esc_html__( 'Permanent / Manual Token', 'adstn-auto-poster' );
							}
							?>
						</strong>
					</div>
					<div class="adstn-info-tile">
						<span class="adstn-info-title"><?php esc_html_e( 'Active Scopes', 'adstn-auto-poster' ); ?></span>
						<code style="font-size: 11px;">user.content.write, user.profile.read</code>
					</div>
				</div>
			</div>
		</div>
	<?php endif; ?>

	<!-- Save Action Sticky Footer -->
	<div class="adstn-form-footer" style="margin-top: 24px;">
		<button type="submit" id="adstn-save-connection-btn" class="adstn-btn adstn-btn-primary adstn-btn-lg">
			<span class="dashicons dashicons-saved"></span>
			<span><?php esc_html_e( 'Save Connection Settings', 'adstn-auto-poster' ); ?></span>
		</button>
		<button type="button" id="adstn-test-connection-btn" class="adstn-btn adstn-btn-secondary adstn-btn-lg">
			<span class="dashicons dashicons-yes-alt"></span>
			<span><?php esc_html_e( 'Test ADStn Connection', 'adstn-auto-poster' ); ?></span>
		</button>
	</div>

</form>
