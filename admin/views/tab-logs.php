<?php
/**
 * Tab: Activity Logs & Publishing History.
 *
 * @package ADStn_Auto_Poster
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$page_num  = isset( $_GET['paged'] ) ? max( 1, (int) $_GET['paged'] ) : 1;
$filter_st = isset( $_GET['status_filter'] ) ? sanitize_text_field( wp_unslash( $_GET['status_filter'] ) ) : '';
$search_q  = isset( $_GET['s_log'] ) ? sanitize_text_field( wp_unslash( $_GET['s_log'] ) ) : '';

$logs_data = ADStn_Logger::get_logs( array(
	'page'     => $page_num,
	'per_page' => 15,
	'status'   => $filter_st,
	'search'   => $search_q,
) );

$items       = $logs_data['items'];
$total_items = $logs_data['total_items'];
$total_pages = $logs_data['total_pages'];
?>

<div class="adstn-card">

	<!-- Toolbar & Filters -->
	<div class="adstn-card-header" style="flex-wrap: wrap; gap: 12px;">
		<h2 class="adstn-card-title">
			<span class="dashicons dashicons-list-view"></span>
			<?php esc_html_e( 'Activity & Sync Logs', 'adstn-auto-poster' ); ?>
			<span class="adstn-badge adstn-badge-info"><?php echo esc_html( $total_items ); ?></span>
		</h2>

		<div class="adstn-logs-toolbar">
			<form method="get" class="adstn-logs-filter-form" style="display: flex; gap: 8px; flex-wrap: wrap;">
				<input type="hidden" name="page" value="adstn-auto-poster">
				<input type="hidden" name="tab" value="logs">

				<input type="text" name="s_log" value="<?php echo esc_attr( $search_q ); ?>" class="adstn-input adstn-input-sm" placeholder="<?php esc_attr_e( 'Search logs...', 'adstn-auto-poster' ); ?>" style="width: 180px;">

				<select name="status_filter" class="adstn-select adstn-select-sm">
					<option value=""><?php esc_html_e( 'All Statuses', 'adstn-auto-poster' ); ?></option>
					<option value="success" <?php selected( $filter_st, 'success' ); ?>><?php esc_html_e( 'Success Only', 'adstn-auto-poster' ); ?></option>
					<option value="failed" <?php selected( $filter_st, 'failed' ); ?>><?php esc_html_e( 'Failed Only', 'adstn-auto-poster' ); ?></option>
				</select>

				<button type="submit" class="adstn-btn-sm adstn-btn-secondary">
					<span class="dashicons dashicons-search"></span>
					<?php esc_html_e( 'Filter', 'adstn-auto-poster' ); ?>
				</button>

				<?php if ( ! empty( $filter_st ) || ! empty( $search_q ) ) : ?>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=adstn-auto-poster&tab=logs' ) ); ?>" class="adstn-btn-sm adstn-btn-secondary">
						<?php esc_html_e( 'Reset', 'adstn-auto-poster' ); ?>
					</a>
				<?php endif; ?>
			</form>

			<?php if ( ! empty( $items ) ) : ?>
				<button type="button" id="adstn-clear-logs-btn" class="adstn-btn-sm adstn-btn-danger">
					<span class="dashicons dashicons-trash"></span>
					<?php esc_html_e( 'Clear Logs', 'adstn-auto-poster' ); ?>
				</button>
			<?php endif; ?>
		</div>
	</div>

	<!-- Table Area -->
	<div class="adstn-card-body padding-none">

		<?php if ( ! empty( $items ) ) : ?>
			<div class="adstn-table-responsive">
				<table class="adstn-table">
					<thead>
						<tr>
							<th style="width: 80px;"><?php esc_html_e( 'Status', 'adstn-auto-poster' ); ?></th>
							<th><?php esc_html_e( 'Post Title', 'adstn-auto-poster' ); ?></th>
							<th><?php esc_html_e( 'Date & Time', 'adstn-auto-poster' ); ?></th>
							<th><?php esc_html_e( 'Response / Result', 'adstn-auto-poster' ); ?></th>
							<th style="text-align: center; width: 140px;"><?php esc_html_e( 'Actions', 'adstn-auto-poster' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $items as $log ) : ?>
							<tr>
								<td>
									<?php if ( 'success' === $log['status'] ) : ?>
										<span class="adstn-status-pill is-active" title="<?php esc_attr_e( 'Published Successfully', 'adstn-auto-poster' ); ?>">
											✓ <?php esc_html_e( 'Success', 'adstn-auto-poster' ); ?>
										</span>
									<?php elseif ( 'failed' === $log['status'] ) : ?>
										<span class="adstn-status-pill is-suspended" title="<?php esc_attr_e( 'Publishing Failed', 'adstn-auto-poster' ); ?>">
											✕ <?php esc_html_e( 'Failed', 'adstn-auto-poster' ); ?>
										</span>
									<?php else : ?>
										<span class="adstn-status-pill is-pending_review">
											⏳ <?php esc_html_e( 'Pending', 'adstn-auto-poster' ); ?>
										</span>
									<?php endif; ?>
								</td>

								<td>
									<?php if ( ! empty( $log['post_id'] ) ) : ?>
										<strong>
											<a href="<?php echo esc_url( get_edit_post_link( $log['post_id'] ) ); ?>" target="_blank">
												<?php echo esc_html( ! empty( $log['post_title'] ) ? $log['post_title'] : __( 'Post #' . $log['post_id'], 'adstn-auto-poster' ) ); ?>
											</a>
										</strong>
										<span style="font-size: 11px; color: var(--adstn-text-muted); display: block;">ID: #<?php echo esc_html( $log['post_id'] ); ?></span>
									<?php else : ?>
										<span><?php echo esc_html( $log['post_title'] ); ?></span>
									<?php endif; ?>
								</td>

								<td style="font-size: 12px; color: var(--adstn-text-muted);">
									<?php echo esc_html( $log['created_at'] ); ?>
								</td>

								<td style="font-size: 12px;">
									<?php if ( 'success' === $log['status'] ) : ?>
										<span style="color: #10d876; font-weight: 600;">
											<?php esc_html_e( 'Content published and verified on ADStn', 'adstn-auto-poster' ); ?>
										</span>
									<?php elseif ( ! empty( $log['error_message'] ) ) : ?>
										<span style="color: #e94b5f; font-weight: 600;">
											<?php echo esc_html( $log['error_message'] ); ?>
										</span>
									<?php else : ?>
										<span>—</span>
									<?php endif; ?>
								</td>

								<td style="text-align: center;">
									<div style="display: flex; justify-content: center; gap: 6px;">
										<button type="button" class="adstn-btn-icon js-view-log-details" data-log-id="<?php echo esc_attr( $log['id'] ); ?>" title="<?php esc_attr_e( 'View Request & Response Details', 'adstn-auto-poster' ); ?>">
											<span class="dashicons dashicons-visibility"></span>
										</button>

										<?php if ( 'failed' === $log['status'] && ! empty( $log['post_id'] ) ) : ?>
											<button type="button" class="adstn-btn-icon js-retry-log" data-log-id="<?php echo esc_attr( $log['id'] ); ?>" title="<?php esc_attr_e( 'Retry Publish Now', 'adstn-auto-poster' ); ?>" style="color: #615dfa;">
												<span class="dashicons dashicons-image-rotate"></span>
											</button>
										<?php endif; ?>
									</div>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>

			<!-- Pagination -->
			<?php if ( $total_pages > 1 ) : ?>
				<div class="adstn-pagination">
					<?php
					$base_url = admin_url( 'admin.php?page=adstn-auto-poster&tab=logs' );
					if ( ! empty( $filter_st ) ) {
						$base_url = add_query_arg( 'status_filter', $filter_st, $base_url );
					}
					if ( ! empty( $search_q ) ) {
						$base_url = add_query_arg( 's_log', $search_q, $base_url );
					}

					for ( $i = 1; $i <= $total_pages; $i++ ) {
						$page_url = add_query_arg( 'paged', $i, $base_url );
						$is_cur   = ( $page_num === $i );
						?>
						<a href="<?php echo esc_url( $page_url ); ?>" class="adstn-page-link <?php echo $is_cur ? 'is-active' : ''; ?>">
							<?php echo esc_html( $i ); ?>
						</a>
					<?php } ?>
				</div>
			<?php endif; ?>

		<?php else : ?>
			<div class="adstn-empty-state">
				<div class="adstn-empty-icon">
					<span class="dashicons dashicons-list-view"></span>
				</div>
				<h3><?php esc_html_e( 'No Logs Recorded Yet', 'adstn-auto-poster' ); ?></h3>
				<p><?php esc_html_e( 'When you publish new articles, every auto-publishing attempt will be recorded here in detail.', 'adstn-auto-poster' ); ?></p>
			</div>
		<?php endif; ?>

	</div>

</div>
