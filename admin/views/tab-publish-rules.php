<?php
/**
 * Tab: Auto-Publishing Rules.
 *
 * @package ADStn_Auto_Poster
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$adstn_is_enabled       = ! empty( $settings['enabled'] ) && '1' === (string) $settings['enabled'];
$adstn_saved_post_types = ! empty( $settings['post_types'] ) && is_array( $settings['post_types'] ) ? $settings['post_types'] : array( 'post' );
$adstn_saved_events     = ! empty( $settings['post_events'] ) && is_array( $settings['post_events'] ) ? $settings['post_events'] : array( 'publish' );
$adstn_privacy          = ! empty( $settings['privacy'] ) ? $settings['privacy'] : 'public';
$adstn_include_cats     = ! empty( $settings['include_categories'] ) && is_array( $settings['include_categories'] ) ? array_map( 'intval', $settings['include_categories'] ) : array();
$adstn_exclude_cats     = ! empty( $settings['exclude_categories'] ) && is_array( $settings['exclude_categories'] ) ? array_map( 'intval', $settings['exclude_categories'] ) : array();

// Fetch all public post types
$adstn_available_post_types = get_post_types( array( 'public' => true ), 'objects' );
unset( $adstn_available_post_types['attachment'] );

// Fetch all categories
$adstn_all_categories = get_categories( array( 'hide_empty' => false ) );
?>

<form id="adstn-rules-form" class="adstn-form" method="post">

	<!-- Main Master Switch -->
	<div class="adstn-card">
		<div class="adstn-card-body">
			<div class="adstn-switch-row">
				<div class="adstn-switch-info">
					<h3 style="margin:0 0 4px; font-size: 16px; color: var(--adstn-text-title);">
						<?php esc_html_e( 'Enable Global Auto-Publishing to ADStn', 'adstn-auto-poster' ); ?>
					</h3>
					<p style="margin:0; font-size: 13px; color: var(--adstn-text-muted);">
						<?php esc_html_e( 'When enabled, eligible articles will be automatically posted to ADStn upon publishing.', 'adstn-auto-poster' ); ?>
					</p>
				</div>
				<label class="adstn-toggle-switch">
					<input type="checkbox" name="enabled" value="1" <?php checked( $adstn_is_enabled, true ); ?>>
					<span class="adstn-toggle-slider"></span>
				</label>
			</div>
		</div>
	</div>

	<!-- Post Types & Triggers Card -->
	<div class="adstn-card" style="margin-top: 24px;">
		<div class="adstn-card-header">
			<h2 class="adstn-card-title">
				<span class="dashicons dashicons-admin-post"></span>
				<?php esc_html_e( 'Post Types & Trigger Events', 'adstn-auto-poster' ); ?>
			</h2>
		</div>
		<div class="adstn-card-body">

			<div class="adstn-form-group">
				<label class="adstn-label"><?php esc_html_e( 'Supported Post Types:', 'adstn-auto-poster' ); ?></label>
				<div class="adstn-checkbox-grid">
					<?php foreach ( $adstn_available_post_types as $adstn_pt_slug => $adstn_pt_obj ) : ?>
						<label class="adstn-checkbox-card">
							<input type="checkbox" name="post_types[]" value="<?php echo esc_attr( $adstn_pt_slug ); ?>" <?php checked( in_array( $adstn_pt_slug, $adstn_saved_post_types, true ) ); ?>>
							<div>
								<strong><?php echo esc_html( $adstn_pt_obj->labels->name ); ?></strong>
								<code style="font-size: 10px; color: var(--adstn-text-muted); display: block;"><?php echo esc_html( $adstn_pt_slug ); ?></code>
							</div>
						</label>
					<?php endforeach; ?>
				</div>
			</div>

			<div class="adstn-form-group" style="margin-top: 20px;">
				<label class="adstn-label"><?php esc_html_e( 'Publish Trigger Events:', 'adstn-auto-poster' ); ?></label>
				<div class="adstn-checkbox-grid">
					<label class="adstn-checkbox-card">
						<input type="checkbox" name="post_events[]" value="publish" <?php checked( in_array( 'publish', $adstn_saved_events, true ) ); ?>>
						<div>
							<strong><?php esc_html_e( 'When a new post is published for the first time', 'adstn-auto-poster' ); ?></strong>
							<span style="font-size: 11px; color: var(--adstn-text-muted); display: block;"><?php esc_html_e( 'Draft / Pending &rarr; Publish', 'adstn-auto-poster' ); ?></span>
						</div>
					</label>

					<label class="adstn-checkbox-card">
						<input type="checkbox" name="post_events[]" value="update" <?php checked( in_array( 'update', $adstn_saved_events, true ) ); ?>>
						<div>
							<strong><?php esc_html_e( 'When an already published post is updated', 'adstn-auto-poster' ); ?></strong>
							<span style="font-size: 11px; color: var(--adstn-text-muted); display: block;"><?php esc_html_e( 'Publish &rarr; Update', 'adstn-auto-poster' ); ?></span>
						</div>
					</label>
				</div>
			</div>

		</div>
	</div>

	<!-- Taxonomy & Category Filters Card -->
	<div class="adstn-card" style="margin-top: 24px;">
		<div class="adstn-card-header">
			<h2 class="adstn-card-title">
				<span class="dashicons dashicons-category"></span>
				<?php esc_html_e( 'Category Filters', 'adstn-auto-poster' ); ?>
			</h2>
		</div>
		<div class="adstn-card-body">

			<div class="adstn-grid-2">
				<div class="adstn-form-group">
					<label class="adstn-label">
						<?php esc_html_e( 'Include Specific Categories Only:', 'adstn-auto-poster' ); ?>
					</label>
					<span class="adstn-help-text" style="margin-bottom: 8px;"><?php esc_html_e( 'Leave empty to automatically include all categories.', 'adstn-auto-poster' ); ?></span>
					<div class="adstn-cat-select-box">
						<?php if ( ! empty( $adstn_all_categories ) ) : ?>
							<?php foreach ( $adstn_all_categories as $cat ) : ?>
								<label class="adstn-cat-item">
									<input type="checkbox" name="include_categories[]" value="<?php echo esc_attr( $cat->term_id ); ?>" <?php checked( in_array( (int) $cat->term_id, $adstn_include_cats, true ) ); ?>>
									<span><?php echo esc_html( $cat->name ); ?> (<?php echo esc_html( $cat->count ); ?>)</span>
								</label>
							<?php endforeach; ?>
						<?php else : ?>
							<p style="font-size: 12px; color: var(--adstn-text-muted); padding: 8px;"><?php esc_html_e( 'No categories found.', 'adstn-auto-poster' ); ?></p>
						<?php endif; ?>
					</div>
				</div>

				<div class="adstn-form-group">
					<label class="adstn-label">
						<?php esc_html_e( 'Exclude Specific Categories:', 'adstn-auto-poster' ); ?>
					</label>
					<span class="adstn-help-text" style="margin-bottom: 8px;"><?php esc_html_e( 'Articles belonging to excluded categories will never be shared to ADStn.', 'adstn-auto-poster' ); ?></span>
					<div class="adstn-cat-select-box">
						<?php if ( ! empty( $adstn_all_categories ) ) : ?>
							<?php foreach ( $adstn_all_categories as $cat ) : ?>
								<label class="adstn-cat-item">
									<input type="checkbox" name="exclude_categories[]" value="<?php echo esc_attr( $cat->term_id ); ?>" <?php checked( in_array( (int) $cat->term_id, $adstn_exclude_cats, true ) ); ?>>
									<span><?php echo esc_html( $cat->name ); ?> (<?php echo esc_html( $cat->count ); ?>)</span>
								</label>
							<?php endforeach; ?>
						<?php else : ?>
							<p style="font-size: 12px; color: var(--adstn-text-muted); padding: 8px;"><?php esc_html_e( 'No categories found.', 'adstn-auto-poster' ); ?></p>
						<?php endif; ?>
					</div>
				</div>
			</div>

		</div>
	</div>

	<!-- Default Privacy Card -->
	<div class="adstn-card" style="margin-top: 24px;">
		<div class="adstn-card-header">
			<h2 class="adstn-card-title">
				<span class="dashicons dashicons-visibility"></span>
				<?php esc_html_e( 'Default Privacy Level on ADStn', 'adstn-auto-poster' ); ?>
			</h2>
		</div>
		<div class="adstn-card-body">
			<div class="adstn-privacy-grid">
				<label class="adstn-privacy-card <?php echo 'public' === $adstn_privacy ? 'is-active' : ''; ?>">
					<input type="radio" name="privacy" value="public" <?php checked( $adstn_privacy, 'public' ); ?>>
					<div class="adstn-privacy-icon">🌐</div>
					<div>
						<strong><?php esc_html_e( 'Public (All Members & Guests)', 'adstn-auto-poster' ); ?></strong>
						<p><?php esc_html_e( 'Visible to everyone on the ADStn community feed and search engines (highest reach).', 'adstn-auto-poster' ); ?></p>
					</div>
				</label>

				<label class="adstn-privacy-card <?php echo 'followers' === $adstn_privacy ? 'is-active' : ''; ?>">
					<input type="radio" name="privacy" value="followers" <?php checked( $adstn_privacy, 'followers' ); ?>>
					<div class="adstn-privacy-icon">👥</div>
					<div>
						<strong><?php esc_html_e( 'Followers Only', 'adstn-auto-poster' ); ?></strong>
						<p><?php esc_html_e( 'Visible only to your account followers on ADStn.', 'adstn-auto-poster' ); ?></p>
					</div>
				</label>

				<label class="adstn-privacy-card <?php echo 'private' === $adstn_privacy ? 'is-active' : ''; ?>">
					<input type="radio" name="privacy" value="private" <?php checked( $adstn_privacy, 'private' ); ?>>
					<div class="adstn-privacy-icon">🔒</div>
					<div>
						<strong><?php esc_html_e( 'Private (Only Me)', 'adstn-auto-poster' ); ?></strong>
						<p><?php esc_html_e( 'Visible only to you in your ADStn account.', 'adstn-auto-poster' ); ?></p>
					</div>
				</label>
			</div>
		</div>
	</div>

	<!-- Save Action Sticky Footer -->
	<div class="adstn-form-footer" style="margin-top: 24px;">
		<button type="submit" id="adstn-save-rules-btn" class="adstn-btn adstn-btn-primary adstn-btn-lg">
			<span class="dashicons dashicons-saved"></span>
			<span><?php esc_html_e( 'Save Publishing Rules', 'adstn-auto-poster' ); ?></span>
		</button>
	</div>

</form>
