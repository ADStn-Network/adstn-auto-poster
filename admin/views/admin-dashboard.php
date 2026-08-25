<?php
/**
 * Main Admin Dashboard View.
 *
 * @package ADStn_Auto_Poster
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$tab_names = array(
	'overview'   => array( 'title' => __( 'Overview & Analytics', 'adstn-auto-poster' ), 'icon' => 'dashicons-dashboard' ),
	'connection' => array( 'title' => __( 'Connection & API', 'adstn-auto-poster' ), 'icon' => 'dashicons-admin-links' ),
	'rules'      => array( 'title' => __( 'Publishing Rules', 'adstn-auto-poster' ), 'icon' => 'dashicons-filter' ),
	'template'   => array( 'title' => __( 'Content Template', 'adstn-auto-poster' ), 'icon' => 'dashicons-edit' ),
	'logs'       => array( 'title' => __( 'Activity Logs', 'adstn-auto-poster' ), 'icon' => 'dashicons-list-view' ),
	'help'       => array( 'title' => __( 'Integration Guide', 'adstn-auto-poster' ), 'icon' => 'dashicons-book' ),
);
?>

<div class="wrap adstn-admin-wrap" dir="<?php echo is_rtl() ? 'rtl' : 'ltr'; ?>">

	<!-- Top Header Banner -->
	<header class="adstn-header-card">
		<div class="adstn-header-main">
			<div class="adstn-logo-box">
				<div class="adstn-logo-icon">
					<svg viewBox="0 0 24 24" fill="currentColor" width="28" height="28">
						<path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 14.5v-9l6 4.5-6 4.5z"/>
					</svg>
				</div>
				<div>
					<h1 class="adstn-app-title">ADStn Auto Poster</h1>
					<p class="adstn-app-subtitle"><?php esc_html_e( 'Auto-publish and instantly synchronize WordPress articles with ADStn platform', 'adstn-auto-poster' ); ?></p>
				</div>
			</div>

			<!-- Status Pill & Quick Actions -->
			<div class="adstn-header-actions">
				<?php if ( $is_connected ) : ?>
					<div class="adstn-status-badge is-connected">
						<span class="adstn-pulse-dot"></span>
						<span><?php esc_html_e( 'Account Connected', 'adstn-auto-poster' ); ?></span>
					</div>
				<?php else : ?>
					<div class="adstn-status-badge is-disconnected">
						<span class="adstn-pulse-dot red"></span>
						<span><?php esc_html_e( 'Not Connected', 'adstn-auto-poster' ); ?></span>
					</div>
				<?php endif; ?>

				<button type="button" id="adstn-quick-test-btn" class="adstn-btn adstn-btn-secondary">
					<span class="dashicons dashicons-update"></span>
					<span><?php esc_html_e( 'Test Connection', 'adstn-auto-poster' ); ?></span>
				</button>
			</div>
		</div>

		<!-- Flash Notice if exists -->
		<?php if ( ! empty( $flash_notice ) ) : ?>
			<div class="adstn-notice adstn-notice-<?php echo esc_attr( $flash_notice['type'] ); ?>">
				<div class="adstn-notice-content">
					<?php echo esc_html( $flash_notice['message'] ); ?>
				</div>
			</div>
		<?php endif; ?>

		<!-- Nav Tabs -->
		<nav class="adstn-nav-tabs">
			<?php foreach ( $tab_names as $tab_key => $tab_info ) : ?>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=adstn-auto-poster&tab=' . $tab_key ) ); ?>"
				   class="adstn-tab-link <?php echo $current_tab === $tab_key ? 'is-active' : ''; ?>">
					<span class="dashicons <?php echo esc_attr( $tab_info['icon'] ); ?>"></span>
					<span><?php echo esc_html( $tab_info['title'] ); ?></span>
				</a>
			<?php endforeach; ?>
		</nav>
	</header>

	<!-- Main Content Area -->
	<main class="adstn-tab-body">
		<?php
		switch ( $current_tab ) {
			case 'connection':
				include plugin_dir_path( __FILE__ ) . 'tab-connection.php';
				break;
			case 'rules':
				include plugin_dir_path( __FILE__ ) . 'tab-publish-rules.php';
				break;
			case 'template':
				include plugin_dir_path( __FILE__ ) . 'tab-template.php';
				break;
			case 'logs':
				include plugin_dir_path( __FILE__ ) . 'tab-logs.php';
				break;
			case 'help':
				include plugin_dir_path( __FILE__ ) . 'tab-help.php';
				break;
			case 'overview':
			default:
				include plugin_dir_path( __FILE__ ) . 'tab-overview.php';
				break;
		}
		?>
	</main>

	<!-- Toast Notification Container -->
	<div id="adstn-toast-container" class="adstn-toast-container"></div>

	<!-- Log Details Modal -->
	<div id="adstn-log-modal" class="adstn-modal" style="display:none;">
		<div class="adstn-modal-overlay"></div>
		<div class="adstn-modal-box">
			<div class="adstn-modal-header">
				<h3 id="adstn-modal-title"><?php esc_html_e( 'Action Details', 'adstn-auto-poster' ); ?></h3>
				<button type="button" class="adstn-modal-close">&times;</button>
			</div>
			<div class="adstn-modal-body" id="adstn-modal-body">
				<!-- Injected by JS -->
			</div>
		</div>
	</div>

</div>
