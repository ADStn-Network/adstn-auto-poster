<?php
/**
 * Tab: Post Template Builder & Live Preview.
 *
 * @package ADStn_Auto_Poster
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$template       = ! empty( $settings['message_template'] ) ? $settings['message_template'] : "{title}\n\n{excerpt}\n\n{url}";
$excerpt_length = isset( $settings['excerpt_length'] ) ? (int) $settings['excerpt_length'] : 160;
$hashtags_mode  = ! empty( $settings['hashtags_mode'] ) ? $settings['hashtags_mode'] : 'tags';
$max_hashtags   = isset( $settings['max_hashtags'] ) ? (int) $settings['max_hashtags'] : 5;

$sample_preview = ADStn_Publisher::generate_sample_preview( $template );
$user_name      = ! empty( $connected_user['name'] ) ? $connected_user['name'] : ( ! empty( $connected_user['username'] ) ? $connected_user['username'] : get_bloginfo( 'name' ) );
$user_avatar    = ! empty( $connected_user['avatar'] ) ? $connected_user['avatar'] : '';
$is_verified    = ! empty( $connected_user['verified'] );
?>

<form id="adstn-template-form" class="adstn-form" method="post">

	<div class="adstn-grid-2">

		<!-- Left Column: Template Editor & Options -->
		<div class="adstn-col">

			<div class="adstn-card">
				<div class="adstn-card-header">
					<h2 class="adstn-card-title">
						<span class="dashicons dashicons-edit"></span>
						<?php esc_html_e( 'Message Template Builder', 'adstn-auto-poster' ); ?>
					</h2>
				</div>
				<div class="adstn-card-body">

					<!-- Available Variable Chips -->
					<div class="adstn-form-group">
						<label class="adstn-label">
							<?php esc_html_e( 'Available Dynamic Tags (Click to insert):', 'adstn-auto-poster' ); ?>
						</label>
						<div class="adstn-chips-container">
							<button type="button" class="adstn-chip js-insert-tag" data-tag="{title}" title="<?php esc_attr_e( 'Article Title', 'adstn-auto-poster' ); ?>">
								<code>{title}</code> <?php esc_html_e( 'Title', 'adstn-auto-poster' ); ?>
							</button>
							<button type="button" class="adstn-chip js-insert-tag" data-tag="{url}" title="<?php esc_attr_e( 'Direct Article Permalink', 'adstn-auto-poster' ); ?>">
								<code>{url}</code> <?php esc_html_e( 'URL', 'adstn-auto-poster' ); ?>
							</button>
							<button type="button" class="adstn-chip js-insert-tag" data-tag="{excerpt}" title="<?php esc_attr_e( 'Article Excerpt or Summary', 'adstn-auto-poster' ); ?>">
								<code>{excerpt}</code> <?php esc_html_e( 'Excerpt', 'adstn-auto-poster' ); ?>
							</button>
							<button type="button" class="adstn-chip js-insert-tag" data-tag="{hashtags}" title="<?php esc_attr_e( 'Auto-generated Hashtags', 'adstn-auto-poster' ); ?>">
								<code>{hashtags}</code> <?php esc_html_e( 'Hashtags', 'adstn-auto-poster' ); ?>
							</button>
							<button type="button" class="adstn-chip js-insert-tag" data-tag="{author}" title="<?php esc_attr_e( 'Post Author Display Name', 'adstn-auto-poster' ); ?>">
								<code>{author}</code> <?php esc_html_e( 'Author', 'adstn-auto-poster' ); ?>
							</button>
							<button type="button" class="adstn-chip js-insert-tag" data-tag="{categories}" title="<?php esc_attr_e( 'Comma-separated Categories', 'adstn-auto-poster' ); ?>">
								<code>{categories}</code> <?php esc_html_e( 'Categories', 'adstn-auto-poster' ); ?>
							</button>
							<button type="button" class="adstn-chip js-insert-tag" data-tag="{tags}" title="<?php esc_attr_e( 'Comma-separated Tags', 'adstn-auto-poster' ); ?>">
								<code>{tags}</code> <?php esc_html_e( 'Tags', 'adstn-auto-poster' ); ?>
							</button>
							<button type="button" class="adstn-chip js-insert-tag" data-tag="{site_name}" title="<?php esc_attr_e( 'WordPress Site Title', 'adstn-auto-poster' ); ?>">
								<code>{site_name}</code> <?php esc_html_e( 'Site Name', 'adstn-auto-poster' ); ?>
							</button>
						</div>
					</div>

					<!-- Textarea -->
					<div class="adstn-form-group" style="margin-top: 14px;">
						<label for="adstn-message-template" class="adstn-label">
							<?php esc_html_e( 'Template Format:', 'adstn-auto-poster' ); ?>
							<span class="adstn-req">*</span>
						</label>
						<textarea id="adstn-message-template" name="message_template" rows="8" class="adstn-textarea" style="font-family: inherit; font-size: 14px; line-height: 1.6;"><?php echo esc_textarea( $template ); ?></textarea>
						<div class="adstn-char-counter">
							<span id="adstn-char-count">0</span> <?php esc_html_e( 'characters (approx.)', 'adstn-auto-poster' ); ?>
						</div>
					</div>

				</div>
			</div>

			<!-- Excerpt & Hashtags Advanced Settings -->
			<div class="adstn-card" style="margin-top: 24px;">
				<div class="adstn-card-header">
					<h2 class="adstn-card-title">
						<span class="dashicons dashicons-admin-generic"></span>
						<?php esc_html_e( 'Excerpt & Hashtags Configuration', 'adstn-auto-poster' ); ?>
					</h2>
				</div>
				<div class="adstn-card-body">

					<div class="adstn-form-group">
						<label for="adstn-excerpt-length" class="adstn-label">
							<?php esc_html_e( 'Maximum Excerpt Length {excerpt} in characters:', 'adstn-auto-poster' ); ?>
						</label>
						<div style="display: flex; align-items: center; gap: 12px;">
							<input type="range" id="adstn-excerpt-range" min="50" max="500" step="10" value="<?php echo esc_attr( $excerpt_length ); ?>" style="flex:1;">
							<input type="number" id="adstn-excerpt-length" name="excerpt_length" class="adstn-input" value="<?php echo esc_attr( $excerpt_length ); ?>" min="50" max="1000" style="width: 90px; text-align: center;">
						</div>
					</div>

					<div class="adstn-form-grid" style="margin-top: 18px;">
						<div class="adstn-form-group">
							<label for="adstn-hashtags-mode" class="adstn-label">
								<?php esc_html_e( 'Generate Hashtags {hashtags} From:', 'adstn-auto-poster' ); ?>
							</label>
							<select id="adstn-hashtags-mode" name="hashtags_mode" class="adstn-select">
								<option value="tags" <?php selected( $hashtags_mode, 'tags' ); ?>><?php esc_html_e( 'Post Tags Only', 'adstn-auto-poster' ); ?></option>
								<option value="categories" <?php selected( $hashtags_mode, 'categories' ); ?>><?php esc_html_e( 'Post Categories Only', 'adstn-auto-poster' ); ?></option>
								<option value="both" <?php selected( $hashtags_mode, 'both' ); ?>><?php esc_html_e( 'Both Tags and Categories', 'adstn-auto-poster' ); ?></option>
								<option value="none" <?php selected( $hashtags_mode, 'none' ); ?>><?php esc_html_e( 'No Hashtags', 'adstn-auto-poster' ); ?></option>
							</select>
						</div>

						<div class="adstn-form-group">
							<label for="adstn-max-hashtags" class="adstn-label">
								<?php esc_html_e( 'Maximum Hashtag Count:', 'adstn-auto-poster' ); ?>
							</label>
							<input type="number" id="adstn-max-hashtags" name="max_hashtags" class="adstn-input" value="<?php echo esc_attr( $max_hashtags ); ?>" min="0" max="30">
						</div>
					</div>

				</div>
			</div>

		</div>

		<!-- Right Column: Live ADStn Simulation Feed Preview -->
		<div class="adstn-col">

			<div class="adstn-card adstn-sticky-card">
				<div class="adstn-card-header">
					<h2 class="adstn-card-title">
						<span class="dashicons dashicons-desktop"></span>
						<?php esc_html_e( 'Live Preview on ADStn Platform', 'adstn-auto-poster' ); ?>
					</h2>
					<span class="adstn-badge adstn-badge-info"><?php esc_html_e( 'Real-time', 'adstn-auto-poster' ); ?></span>
				</div>
				<div class="adstn-card-body" style="background: #f8faff; padding: 24px;">

					<p style="font-size: 12px; color: var(--adstn-text-muted); margin-top: 0; margin-bottom: 16px;">
						<?php esc_html_e( 'Simulated preview of how your shared post will look on ADStn:', 'adstn-auto-poster' ); ?>
					</p>

					<!-- Simulated ADStn Post Card -->
					<div class="adstn-simulated-post">
						<!-- Post Head -->
						<div class="adstn-sim-header">
							<div class="adstn-sim-avatar">
								<?php if ( ! empty( $user_avatar ) ) : ?>
									<img src="<?php echo esc_url( $user_avatar ); ?>" alt="Avatar">
								<?php else : ?>
									<div class="adstn-sim-avatar-fallback"><?php echo esc_html( mb_substr( $user_name, 0, 1 ) ); ?></div>
								<?php endif; ?>
							</div>
							<div class="adstn-sim-user">
								<div class="adstn-sim-name">
									<strong><?php echo esc_html( $user_name ); ?></strong>
									<?php if ( $is_verified ) : ?>
										<span class="adstn-verified-mini" title="Verified">✓</span>
									<?php endif; ?>
								</div>
								<div class="adstn-sim-time"><?php esc_html_e( 'Just now • Auto-shared from WordPress', 'adstn-auto-poster' ); ?></div>
							</div>
							<div class="adstn-sim-opt">•••</div>
						</div>

						<!-- Post Body / Content -->
						<div class="adstn-sim-body" id="adstn-sim-content">
							<?php echo nl2br( esc_html( $sample_preview ) ); ?>
						</div>

						<!-- Simulated Link Attachment Box -->
						<div class="adstn-sim-attachment">
							<div class="adstn-sim-att-icon">🔗</div>
							<div class="adstn-sim-att-info">
								<span class="adstn-sim-att-domain"><?php echo esc_html( wp_parse_url( home_url(), PHP_URL_HOST ) ); ?></span>
								<strong class="adstn-sim-att-title" id="adstn-sim-att-title"><?php esc_html_e( 'Sample Article Title for Auto-Publishing', 'adstn-auto-poster' ); ?></strong>
								<span class="adstn-sim-att-desc"><?php esc_html_e( 'Click here to read the full article on our website...', 'adstn-auto-poster' ); ?></span>
							</div>
						</div>

						<!-- Post Footer Actions (Like, Comment, Share) -->
						<div class="adstn-sim-footer">
							<div class="adstn-sim-action">
								<span>❤️</span> <?php esc_html_e( 'Like', 'adstn-auto-poster' ); ?>
							</div>
							<div class="adstn-sim-action">
								<span>💬</span> <?php esc_html_e( 'Comment', 'adstn-auto-poster' ); ?>
							</div>
							<div class="adstn-sim-action">
								<span>🔄</span> <?php esc_html_e( 'Share', 'adstn-auto-poster' ); ?>
							</div>
						</div>
					</div>

				</div>
			</div>

		</div>

	</div>

	<!-- Save Action Sticky Footer -->
	<div class="adstn-form-footer" style="margin-top: 24px;">
		<button type="submit" id="adstn-save-template-btn" class="adstn-btn adstn-btn-primary adstn-btn-lg">
			<span class="dashicons dashicons-saved"></span>
			<span><?php esc_html_e( 'Save Message Template', 'adstn-auto-poster' ); ?></span>
		</button>
	</div>

</form>
