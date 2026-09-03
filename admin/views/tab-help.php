<?php
/**
 * Tab: Documentation & Integration Guide.
 *
 * @package ADStn_Auto_Poster
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$adstn_redirect_uri = ADStn_API_Client::get_redirect_uri();
?>

<div class="adstn-grid-2">

	<!-- Left Column: Step-by-Step Setup Guide -->
	<div class="adstn-col">

		<div class="adstn-card">
			<div class="adstn-card-header">
				<h2 class="adstn-card-title">
					<span class="dashicons dashicons-book"></span>
					<?php esc_html_e( 'ADStn Integration Walkthrough', 'adstn-auto-poster' ); ?>
				</h2>
			</div>
			<div class="adstn-card-body">

				<div class="adstn-timeline">

					<!-- Step 1 -->
					<div class="adstn-timeline-step">
						<div class="adstn-tstep-badge">1</div>
						<div class="adstn-tstep-content">
							<h4><?php esc_html_e( 'Create an App on ADStn Developer Platform', 'adstn-auto-poster' ); ?></h4>
							<p><?php esc_html_e( 'Log in to your ADStn account and navigate to the developer app creation portal:', 'adstn-auto-poster' ); ?></p>
							<a href="https://www.adstn.ovh/developer/apps/create" target="_blank" class="adstn-btn adstn-btn-secondary adstn-btn-sm" style="margin-top: 6px;">
								<span class="dashicons dashicons-external"></span>
								https://www.adstn.ovh/developer/apps/create
							</a>
						</div>
					</div>

					<!-- Step 2 -->
					<div class="adstn-timeline-step">
						<div class="adstn-tstep-badge">2</div>
						<div class="adstn-tstep-content">
							<h4><?php esc_html_e( 'Enter App Details & Redirect URI', 'adstn-auto-poster' ); ?></h4>
							<p><?php esc_html_e( 'Fill in your application name and domain, then copy and paste this Redirect URI into the authorized callback field:', 'adstn-auto-poster' ); ?></p>
							<div class="adstn-copy-box" style="margin-top: 6px;">
								<input type="text" readonly id="adstn-help-redirect-uri" class="adstn-input" value="<?php echo esc_url( $adstn_redirect_uri ); ?>">
								<button type="button" class="adstn-btn adstn-btn-secondary js-adstn-copy" data-target="#adstn-help-redirect-uri">
									<span class="dashicons dashicons-clipboard"></span>
									<span><?php esc_html_e( 'Copy', 'adstn-auto-poster' ); ?></span>
								</button>
							</div>
						</div>
					</div>

					<!-- Step 3 -->
					<div class="adstn-timeline-step">
						<div class="adstn-tstep-badge">3</div>
						<div class="adstn-tstep-content">
							<h4><?php esc_html_e( 'Select Required OAuth 2.0 Scopes', 'adstn-auto-poster' ); ?></h4>
							<p><?php esc_html_e( 'Ensure you enable the following permissions for your app:', 'adstn-auto-poster' ); ?></p>
							<div style="display: flex; gap: 6px; flex-wrap: wrap; margin-top: 6px;">
								<code class="adstn-badge adstn-badge-info">user.identity.read</code>
																<code class="adstn-badge adstn-badge-success">user.content.write</code>
							</div>
						</div>
					</div>

					<!-- Step 4 -->
					<div class="adstn-timeline-step">
						<div class="adstn-tstep-badge">4</div>
						<div class="adstn-tstep-content">
							<h4><?php esc_html_e( 'Copy Credentials & Authorize', 'adstn-auto-poster' ); ?></h4>
							<p><?php esc_html_e( 'Paste your Client ID and Client Secret into the "Connection" tab of this plugin, then click "Connect & Authorize with ADStn".', 'adstn-auto-poster' ); ?></p>
						</div>
					</div>

				</div>

			</div>
		</div>

	</div>

	<!-- Right Column: Placeholders & Embed Widgets -->
	<div class="adstn-col">

		<!-- Template Placeholders Quick Reference -->
		<div class="adstn-card">
			<div class="adstn-card-header">
				<h2 class="adstn-card-title">
					<span class="dashicons dashicons-editor-code"></span>
					<?php esc_html_e( 'Template Tags Reference', 'adstn-auto-poster' ); ?>
				</h2>
			</div>
			<div class="adstn-card-body padding-none">
				<table class="adstn-table">
					<tbody>
						<tr>
							<td><code>{title}</code></td>
							<td><?php esc_html_e( 'Full title of the published article', 'adstn-auto-poster' ); ?></td>
						</tr>
						<tr>
							<td><code>{url}</code></td>
							<td><?php esc_html_e( 'Direct permalink to the published article', 'adstn-auto-poster' ); ?></td>
						</tr>
						<tr>
							<td><code>{excerpt}</code></td>
							<td><?php esc_html_e( 'Article excerpt or trimmed content summary', 'adstn-auto-poster' ); ?></td>
						</tr>
						<tr>
							<td><code>{hashtags}</code></td>
							<td><?php esc_html_e( 'Automated hashtags generated from tags/categories (#tag)', 'adstn-auto-poster' ); ?></td>
						</tr>
						<tr>
							<td><code>{author}</code></td>
							<td><?php esc_html_e( 'Display name of the post author', 'adstn-auto-poster' ); ?></td>
						</tr>
						<tr>
							<td><code>{site_name}</code></td>
							<td><?php esc_html_e( 'WordPress site title', 'adstn-auto-poster' ); ?></td>
						</tr>
						<tr>
							<td><code>{categories}</code></td>
							<td><?php esc_html_e( 'Comma-separated post category names', 'adstn-auto-poster' ); ?></td>
						</tr>
						<tr>
							<td><code>{tags}</code></td>
							<td><?php esc_html_e( 'Comma-separated post tag names', 'adstn-auto-poster' ); ?></td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>

		<!-- ADStn Developer Documentation Card -->
		<div class="adstn-card" style="margin-top: 24px;">
			<div class="adstn-card-header">
				<h2 class="adstn-card-title">
					<span class="dashicons dashicons-external"></span>
					<?php esc_html_e( 'Official ADStn Developer Resources', 'adstn-auto-poster' ); ?>
				</h2>
			</div>
			<div class="adstn-card-body">
				<p style="font-size: 13px; color: var(--adstn-text-muted); margin-top: 0;">
					<?php esc_html_e( 'Learn more about API endpoints, webhooks, rate limits, and profile widgets in the official developer documentation:', 'adstn-auto-poster' ); ?>
				</p>
				<a href="https://www.adstn.ovh/developer/guides" target="_blank" class="adstn-btn adstn-btn-secondary" style="margin-top: 8px;">
					<span class="dashicons dashicons-book"></span>
					<?php esc_html_e( 'View ADStn Developer Guides', 'adstn-auto-poster' ); ?> &rarr;
				</a>
			</div>
		</div>

	</div>

</div>
