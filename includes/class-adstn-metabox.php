<?php
/**
 * Post Editor Sidebar Metabox for ADStn.
 *
 * @package    ADStn_Auto_Poster
 * @subpackage ADStn_Auto_Poster/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ADStn_Metabox {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'register_meta_box' ) );
		add_action( 'save_post', array( $this, 'save_meta_box' ) );
	}

	/**
	 * Register meta box on allowed post types.
	 */
	public function register_meta_box() {
		$settings   = get_option( 'adstn_settings', array() );
		$post_types = isset( $settings['post_types'] ) && is_array( $settings['post_types'] ) ? $settings['post_types'] : array( 'post' );

		foreach ( $post_types as $post_type ) {
			add_meta_box(
				'adstn_auto_poster_metabox',
				'<span style="display:inline-flex;align-items:center;gap:6px;"><span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#615dfa;"></span> ' . esc_html__( 'ADStn Auto Poster', 'adstn-auto-poster' ) . '</span>',
				array( $this, 'render_meta_box' ),
				$post_type,
				'side',
				'high'
			);
		}
	}

	/**
	 * Render Metabox HTML.
	 *
	 * @param WP_Post $post
	 */
	public function render_meta_box( $post ) {
		wp_nonce_field( 'adstn_metabox_nonce_action', 'adstn_metabox_nonce' );

		$settings     = get_option( 'adstn_settings', array() );
		$api_client   = new ADStn_API_Client( $settings );
		$is_connected = $api_client->is_connected();

		$auto_publish_meta = get_post_meta( $post->ID, '_adstn_auto_publish', true );
		// Default to checked (1) if not set
		$auto_publish = ( '' === $auto_publish_meta || '1' === $auto_publish_meta );

		$custom_message = get_post_meta( $post->ID, '_adstn_custom_message', true );
		$is_published   = get_post_meta( $post->ID, '_adstn_published', true );
		$published_at   = get_post_meta( $post->ID, '_adstn_published_at', true );
		$last_error     = get_post_meta( $post->ID, '_adstn_last_error', true );

		$connected_user = isset( $settings['connected_user'] ) ? $settings['connected_user'] : array();
		?>
		<div class="adstn-metabox-wrapper" style="font-family: inherit; font-size: 13px; line-height: 1.5;">

			<?php if ( ! $is_connected ) : ?>
				<div style="background: #fff3cd; border-inline-start: 4px solid #ffc107; padding: 10px; margin-bottom: 12px; border-radius: 4px; color: #856404;">
					<strong>⚠️ <?php esc_html_e( 'ADStn account not connected', 'adstn-auto-poster' ); ?></strong><br>
					<span style="font-size: 11px;"><?php esc_html_e( 'Please connect your account in plugin settings to enable auto-publishing.', 'adstn-auto-poster' ); ?></span>
					<div style="margin-top: 6px;">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=adstn-auto-poster&tab=connection' ) ); ?>" class="button button-small" target="_blank">
							<?php esc_html_e( 'Connect Account Now', 'adstn-auto-poster' ); ?> &rarr;
						</a>
					</div>
				</div>
			<?php else : ?>
				<div style="display: flex; align-items: center; justify-content: space-between; background: #eef2ff; padding: 8px 10px; border-radius: 6px; margin-bottom: 12px;">
					<div style="display: flex; align-items: center; gap: 8px;">
						<?php if ( ! empty( $connected_user['avatar'] ) ) : ?>
							<img src="<?php echo esc_url( $connected_user['avatar'] ); ?>" style="width: 24px; height: 24px; border-radius: 50%; object-fit: cover;" alt="Avatar">
						<?php else : ?>
							<span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #10d876;"></span>
						<?php endif; ?>
						<span style="font-weight: 600; color: #3730a3; font-size: 12px;">
							<?php echo ! empty( $connected_user['name'] ) ? esc_html( $connected_user['name'] ) : ( ! empty( $connected_user['username'] ) ? '@' . esc_html( $connected_user['username'] ) : esc_html__( 'Connected to ADStn', 'adstn-auto-poster' ) ); ?>
						</span>
					</div>
					<span style="background: #10d876; color: #fff; padding: 2px 6px; border-radius: 10px; font-size: 10px; font-weight: 700;">
						<?php esc_html_e( 'Active', 'adstn-auto-poster' ); ?>
					</span>
				</div>
			<?php endif; ?>

			<!-- Publishing Status Badge -->
			<?php if ( $is_published ) : ?>
				<div style="background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; padding: 8px 10px; border-radius: 6px; margin-bottom: 12px; font-size: 12px;">
					<strong>✓ <?php esc_html_e( 'Published to ADStn', 'adstn-auto-poster' ); ?></strong>
					<?php if ( $published_at ) : ?>
						<div style="font-size: 10px; color: #15803d; margin-top: 2px;">
							<?php
							/* translators: %s: date and time when the post was published to ADStn */
							echo esc_html( sprintf( __( 'Date: %s', 'adstn-auto-poster' ), $published_at ) );
							?>
						</div>
					<?php endif; ?>
				</div>
			<?php elseif ( $last_error ) : ?>
				<div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 8px 10px; border-radius: 6px; margin-bottom: 12px; font-size: 12px;">
					<strong>⚠️ <?php esc_html_e( 'Previous publish failed:', 'adstn-auto-poster' ); ?></strong>
					<div style="font-size: 11px; margin-top: 2px;"><?php echo esc_html( $last_error ); ?></div>
				</div>
			<?php endif; ?>

			<!-- Auto Publish Toggle -->
			<div style="margin-bottom: 12px;">
				<label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-weight: 600; color: #1e293b;">
					<input type="checkbox" name="adstn_auto_publish" value="1" <?php checked( $auto_publish, true ); ?>>
					<span><?php esc_html_e( 'Auto-publish this post to ADStn', 'adstn-auto-poster' ); ?></span>
				</label>
				<p style="margin: 4px 0 0 24px; font-size: 11px; color: #64748b;">
					<?php esc_html_e( 'This post will be automatically shared when published.', 'adstn-auto-poster' ); ?>
				</p>
			</div>

			<!-- Custom Message Collapsible -->
			<div style="margin-bottom: 14px;">
				<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
					<label for="adstn_custom_message" style="font-weight: 600; color: #1e293b; font-size: 12px;">
						<?php esc_html_e( 'Custom Post Message (Optional):', 'adstn-auto-poster' ); ?>
					</label>
				</div>
				<textarea id="adstn_custom_message" name="adstn_custom_message" rows="3" style="width: 100%; border-radius: 6px; border: 1px solid #cbd5e1; padding: 6px 8px; font-size: 12px; box-sizing: border-box;" placeholder="<?php esc_attr_e( 'Leave blank to use default template...', 'adstn-auto-poster' ); ?>"><?php echo esc_textarea( $custom_message ); ?></textarea>
				<div style="font-size: 10px; color: #94a3b8; margin-top: 2px;">
					<?php esc_html_e( 'Supports tags: {title}, {url}, {excerpt}, {hashtags}', 'adstn-auto-poster' ); ?>
				</div>
			</div>

			<!-- Instant Manual Share Action -->
			<?php if ( 'publish' === $post->post_status && $is_connected ) : ?>
				<div style="padding-top: 10px; border-top: 1px dashed #e2e8f0;">
					<button type="button" id="adstn-instant-share-btn" data-post-id="<?php echo esc_attr( $post->ID ); ?>" class="button button-primary" style="width: 100%; background: #615dfa; border-color: #615dfa; display: flex; align-items: center; justify-content: center; gap: 6px; height: 34px;">
						<span class="dashicons dashicons-share" style="font-size: 16px; width: 16px; height: 16px; margin-top: 2px;"></span>
						<span><?php esc_html_e( 'Share to ADStn Now', 'adstn-auto-poster' ); ?></span>
					</button>
					<div id="adstn-instant-share-notice" style="margin-top: 8px; font-size: 11px; display: none;"></div>
				</div>
			<?php endif; ?>

		</div>
		<?php
	}

	/**
	 * Save metabox data.
	 *
	 * @param int $post_id
	 */
	public function save_meta_box( $post_id ) {
		// Verify Nonce
		if ( ! isset( $_POST['adstn_metabox_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['adstn_metabox_nonce'] ) ), 'adstn_metabox_nonce_action' ) ) {
			return;
		}

		// Prevent autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check permissions
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Save Auto Publish flag
		$auto_publish = isset( $_POST['adstn_auto_publish'] ) ? '1' : '0';
		update_post_meta( $post_id, '_adstn_auto_publish', $auto_publish );

		// Save Custom Message
		if ( isset( $_POST['adstn_custom_message'] ) ) {
			$custom_message = sanitize_textarea_field( wp_unslash( $_POST['adstn_custom_message'] ) );
			update_post_meta( $post_id, '_adstn_custom_message', $custom_message );
		}
	}
}
