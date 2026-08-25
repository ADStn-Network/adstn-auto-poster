<?php
/**
 * Tab: Overview & Analytics.
 *
 * @package ADStn_Auto_Poster
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="adstn-grid-2">

	<!-- Left Column: Connected Account & Status -->
	<div class="adstn-col">

		<!-- Account Card -->
		<div class="adstn-card">
			<div class="adstn-card-header">
				<h2 class="adstn-card-title">
					<span class="dashicons dashicons-admin-users"></span>
					<?php esc_html_e( 'Connected ADStn Account', 'adstn-auto-poster' ); ?>
				</h2>
				<?php if ( $is_connected ) : ?>
					<button type="button" id="adstn-refresh-profile-btn" class="adstn-btn-icon" title="<?php esc_attr_e( 'Refresh Profile Details', 'adstn-auto-poster' ); ?>">
						<span class="dashicons dashicons-update"></span>
					</button>
				<?php endif; ?>
			</div>

			<div class="adstn-card-body">
				<?php if ( $is_connected ) : ?>
					<div class="adstn-profile-box">
						<div class="adstn-profile-avatar-wrap">
							<?php if ( ! empty( $connected_user['avatar'] ) ) : ?>
								<img src="<?php echo esc_url( $connected_user['avatar'] ); ?>" class="adstn-profile-avatar" alt="Avatar">
							<?php else : ?>
								<div class="adstn-profile-avatar-placeholder">
									<span class="dashicons dashicons-admin-users"></span>
								</div>
							<?php endif; ?>
							<?php if ( ! empty( $connected_user['verified'] ) ) : ?>
								<span class="adstn-verified-badge" title="<?php esc_attr_e( 'Verified Account', 'adstn-auto-poster' ); ?>">✓</span>
							<?php endif; ?>
						</div>

						<div class="adstn-profile-info">
							<h3 class="adstn-profile-name">
								<?php echo ! empty( $connected_user['name'] ) ? esc_html( $connected_user['name'] ) : ( ! empty( $connected_user['username'] ) ? esc_html( $connected_user['username'] ) : esc_html__( 'ADStn Member', 'adstn-auto-poster' ) ); ?>
							</h3>
							<?php if ( ! empty( $connected_user['username'] ) ) : ?>
								<p class="adstn-profile-handle">@<?php echo esc_html( $connected_user['username'] ); ?></p>
							<?php endif; ?>
							<?php if ( ! empty( $connected_user['email'] ) ) : ?>
								<p class="adstn-profile-meta"><span class="dashicons dashicons-email"></span> <?php echo esc_html( $connected_user['email'] ); ?></p>
							<?php endif; ?>
						</div>
					</div>

					<div class="adstn-profile-stats">
						<div class="adstn-pstat-item">
							<span class="adstn-pstat-val"><?php echo ! empty( $connected_user['followers_count'] ) ? esc_html( $connected_user['followers_count'] ) : '0'; ?></span>
							<span class="adstn-pstat-lbl"><?php esc_html_e( 'Followers', 'adstn-auto-poster' ); ?></span>
						</div>
						<div class="adstn-pstat-item">
							<span class="adstn-pstat-val" style="color: #10d876;"><?php esc_html_e( 'Active', 'adstn-auto-poster' ); ?></span>
							<span class="adstn-pstat-lbl"><?php esc_html_e( 'Token Status', 'adstn-auto-poster' ); ?></span>
						</div>
						<div class="adstn-pstat-item">
							<span class="adstn-pstat-val"><?php echo esc_html( ! empty( $settings['privacy'] ) ? $settings['privacy'] : 'public' ); ?></span>
							<span class="adstn-pstat-lbl"><?php esc_html_e( 'Privacy', 'adstn-auto-poster' ); ?></span>
						</div>
					</div>

					<div class="adstn-profile-actions">
						<a href="https://www.adstn.ovh" target="_blank" class="adstn-btn adstn-btn-secondary" style="flex:1;">
							<span class="dashicons dashicons-external"></span>
							<?php esc_html_e( 'View Profile on ADStn', 'adstn-auto-poster' ); ?>
						</a>
						<button type="button" id="adstn-disconnect-btn" class="adstn-btn adstn-btn-danger">
							<?php esc_html_e( 'Disconnect', 'adstn-auto-poster' ); ?>
						</button>
					</div>

				<?php else : ?>
					<div class="adstn-empty-state">
						<div class="adstn-empty-icon">
							<span class="dashicons dashicons-admin-links"></span>
						</div>
						<h3><?php esc_html_e( 'Account Not Connected Yet', 'adstn-auto-poster' ); ?></h3>
						<p><?php esc_html_e( 'Connect your ADStn account to start auto-publishing your WordPress articles and reach a broader audience.', 'adstn-auto-poster' ); ?></p>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=adstn-auto-poster&tab=connection' ) ); ?>" class="adstn-btn adstn-btn-primary">
							<span class="dashicons dashicons-migrate"></span>
							<?php esc_html_e( 'Go to Connection Settings', 'adstn-auto-poster' ); ?>
						</a>
					</div>
				<?php endif; ?>
			</div>
		</div>

		<!-- Quick Feature Cards -->
		<div class="adstn-card" style="margin-top: 24px;">
			<div class="adstn-card-header">
				<h2 class="adstn-card-title">
					<span class="dashicons dashicons-info"></span>
					<?php esc_html_e( 'ADStn Integration Highlights', 'adstn-auto-poster' ); ?>
				</h2>
			</div>
			<div class="adstn-card-body">
				<ul class="adstn-feature-list">
					<li>
						<span class="adstn-check-icon">✓</span>
						<div>
							<strong><?php esc_html_e( 'Secure OAuth 2.0 Flow', 'adstn-auto-poster' ); ?></strong>
							<p><?php esc_html_e( 'Full token encryption and automatic refresh without storing account passwords.', 'adstn-auto-poster' ); ?></p>
						</div>
					</li>
					<li>
						<span class="adstn-check-icon">✓</span>
						<div>
							<strong><?php esc_html_e( 'Rate Limit Compliance', 'adstn-auto-poster' ); ?></strong>
							<p><?php esc_html_e( 'Built-in handling for ADStn rate limits (30 requests/minute).', 'adstn-auto-poster' ); ?></p>
						</div>
					</li>
					<li>
						<span class="adstn-check-icon">✓</span>
						<div>
							<strong><?php esc_html_e( 'Dynamic Smart Templates', 'adstn-auto-poster' ); ?></strong>
							<p><?php esc_html_e( 'Complete customization of post format with automated hashtag extraction.', 'adstn-auto-poster' ); ?></p>
						</div>
					</li>
				</ul>
			</div>
		</div>

	</div>

	<!-- Right Column: Analytics & Quick Stats -->
	<div class="adstn-col">

		<!-- Stat Counters -->
		<div class="adstn-stat-grid">
			<div class="adstn-stat-card">
				<div class="adstn-stat-icon purple">
					<span class="dashicons dashicons-share-alt"></span>
				</div>
				<div class="adstn-stat-content">
					<span class="adstn-stat-number"><?php echo esc_html( $stats['total'] ); ?></span>
					<span class="adstn-stat-label"><?php esc_html_e( 'Total Shares', 'adstn-auto-poster' ); ?></span>
				</div>
			</div>

			<div class="adstn-stat-card">
				<div class="adstn-stat-icon green">
					<span class="dashicons dashicons-yes-alt"></span>
				</div>
				<div class="adstn-stat-content">
					<span class="adstn-stat-number"><?php echo esc_html( $stats['success'] ); ?></span>
					<span class="adstn-stat-label"><?php esc_html_e( 'Successful Posts', 'adstn-auto-poster' ); ?></span>
				</div>
			</div>

			<div class="adstn-stat-card">
				<div class="adstn-stat-icon red">
					<span class="dashicons dashicons-dismiss"></span>
				</div>
				<div class="adstn-stat-content">
					<span class="adstn-stat-number"><?php echo esc_html( $stats['failed'] ); ?></span>
					<span class="adstn-stat-label"><?php esc_html_e( 'Failed Attempts', 'adstn-auto-poster' ); ?></span>
				</div>
			</div>

			<div class="adstn-stat-card">
				<div class="adstn-stat-icon blue">
					<span class="dashicons dashicons-clock"></span>
				</div>
				<div class="adstn-stat-content">
					<span class="adstn-stat-number" style="font-size: 14px;"><?php echo esc_html( $stats['last_share'] ); ?></span>
					<span class="adstn-stat-label"><?php esc_html_e( 'Last Activity', 'adstn-auto-poster' ); ?></span>
				</div>
			</div>
		</div>

		<!-- Quick Settings Summary Card -->
		<div class="adstn-card" style="margin-top: 24px;">
			<div class="adstn-card-header">
				<h2 class="adstn-card-title">
					<span class="dashicons dashicons-admin-settings"></span>
					<?php esc_html_e( 'Active Settings Summary', 'adstn-auto-poster' ); ?>
				</h2>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=adstn-auto-poster&tab=rules' ) ); ?>" class="adstn-btn-sm adstn-btn-secondary">
					<?php esc_html_e( 'Configure', 'adstn-auto-poster' ); ?> &rarr;
				</a>
			</div>
			<div class="adstn-card-body">
				<div class="adstn-summary-row">
					<span class="adstn-slabel"><?php esc_html_e( 'Auto-Publish Status:', 'adstn-auto-poster' ); ?></span>
					<span class="adstn-svalue">
						<?php if ( ! empty( $settings['enabled'] ) && '1' === (string) $settings['enabled'] ) : ?>
							<span class="adstn-badge adstn-badge-success"><?php esc_html_e( 'Enabled', 'adstn-auto-poster' ); ?></span>
						<?php else : ?>
							<span class="adstn-badge adstn-badge-danger"><?php esc_html_e( 'Disabled', 'adstn-auto-poster' ); ?></span>
						<?php endif; ?>
					</span>
				</div>

				<div class="adstn-summary-row">
					<span class="adstn-slabel"><?php esc_html_e( 'Enabled Post Types:', 'adstn-auto-poster' ); ?></span>
					<span class="adstn-svalue">
						<?php
						$adstn_pts = ! empty( $settings['post_types'] ) ? (array) $settings['post_types'] : array( 'post' );
						echo esc_html( implode( ', ', $adstn_pts ) );
						?>
					</span>
				</div>

				<div class="adstn-summary-row">
					<span class="adstn-slabel"><?php esc_html_e( 'Default Privacy:', 'adstn-auto-poster' ); ?></span>
					<span class="adstn-svalue">
						<code><?php echo esc_html( ! empty( $settings['privacy'] ) ? $settings['privacy'] : 'public' ); ?></code>
					</span>
				</div>

				<div class="adstn-summary-row">
					<span class="adstn-slabel"><?php esc_html_e( 'Hashtags Mode:', 'adstn-auto-poster' ); ?></span>
					<span class="adstn-svalue">
						<?php
						$adstn_hm = ! empty( $settings['hashtags_mode'] ) ? $settings['hashtags_mode'] : 'tags';
						/* translators: %1$s: hashtag mode name, %2$d: maximum number of hashtags */
						echo esc_html( sprintf( __( 'Mode: %1$s (Max: %2$d)', 'adstn-auto-poster' ), $adstn_hm, ! empty( $settings['max_hashtags'] ) ? (int) $settings['max_hashtags'] : 5 ) );
						?>
					</span>
				</div>
			</div>
		</div>

		<!-- Direct Web Share Test Tool -->
		<div class="adstn-card" style="margin-top: 24px;">
			<div class="adstn-card-header">
				<h2 class="adstn-card-title">
					<span class="dashicons dashicons-share"></span>
					<?php esc_html_e( 'Quick Share Tool (ADStn Web Share API)', 'adstn-auto-poster' ); ?>
				</h2>
			</div>
			<div class="adstn-card-body">
				<p style="font-size: 13px; color: var(--adstn-text-muted); margin-bottom: 12px;">
					<?php esc_html_e( 'Test sending text and links to the ADStn share composer directly:', 'adstn-auto-poster' ); ?>
				</p>
				<div style="display: flex; gap: 8px;">
					<input type="text" id="adstn-quick-share-input" class="adstn-input" value="<?php echo esc_attr( get_bloginfo( 'name' ) . ' - ' . home_url() ); ?>" placeholder="<?php esc_attr_e( 'Enter text and URL to share...', 'adstn-auto-poster' ); ?>" style="flex:1;">
					<button type="button" id="adstn-open-share-btn" class="adstn-btn adstn-btn-primary">
						<span class="dashicons dashicons-share"></span>
						<?php esc_html_e( 'Share', 'adstn-auto-poster' ); ?>
					</button>
				</div>
			</div>
		</div>

	</div>

</div>
