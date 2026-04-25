<?php
/**
 * Admin Settings Page Template
 *
 * @package AI_Auto_News_Poster
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$aanp_options      = get_option( 'aanp_settings', array() );
$aanp_post_creator = new AANP_Post_Creator();
$aanp_stats        = $aanp_post_creator->get_stats();
$aanp_recent_posts = $aanp_post_creator->get_recent_posts( 5 );
?>

<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

	<?php settings_errors(); ?>

	<!-- Author / Sponsor Strip -->
	<div class="aanp-author-strip">
		<div class="aanp-author-strip__logo">
			<span class="aanp-author-strip__logo-mark">NF</span>
			<span class="aanp-author-strip__logo-name">NewsForge</span>
		</div>
		<div class="aanp-author-strip__body">
			<p class="aanp-author-strip__credit">
				<?php esc_html_e( 'NewsForge is a free WordPress plugin developed and maintained by', 'newsforge-ai-auto-news-poster' ); ?>
				<a href="https://github.com/arunrajiah" target="_blank" rel="noopener">arunrajiah</a>.
			</p>
			<p class="aanp-author-strip__sponsor">
				<?php esc_html_e( 'If you find it useful, please consider', 'newsforge-ai-auto-news-poster' ); ?>
				<a href="https://github.com/sponsors/arunrajiah" target="_blank" rel="noopener" class="aanp-sponsor-btn">
					<span class="aanp-sponsor-btn__heart">&#9829;</span>
					<?php esc_html_e( 'becoming a sponsor on GitHub', 'newsforge-ai-auto-news-poster' ); ?>
				</a>
				<?php esc_html_e( '— it helps keep the project alive and growing.', 'newsforge-ai-auto-news-poster' ); ?>
			</p>
		</div>
	</div>

	<!-- Pro Features Banner -->
	<div class="notice notice-info">
		<p><strong><?php esc_html_e( 'Upgrade to Pro for Advanced Features!', 'newsforge-ai-auto-news-poster' ); ?></strong></p>
		<ul style="margin-left: 20px;">
			<li><?php esc_html_e( '• Automated scheduling with WP-Cron', 'newsforge-ai-auto-news-poster' ); ?></li>
			<li><?php esc_html_e( '• Generate up to 30 posts per batch', 'newsforge-ai-auto-news-poster' ); ?></li>
			<li><?php esc_html_e( '• Automatic featured image generation', 'newsforge-ai-auto-news-poster' ); ?></li>
			<li><?php esc_html_e( '• SEO meta tags auto-fill', 'newsforge-ai-auto-news-poster' ); ?></li>
			<li><?php esc_html_e( '• Priority support', 'newsforge-ai-auto-news-poster' ); ?></li>
		</ul>
		<p>
			<a href="#" class="button button-primary aanp-pro-upgrade-btn">
				<?php esc_html_e( 'Upgrade to Pro', 'newsforge-ai-auto-news-poster' ); ?>
			</a>
		</p>
	</div>

	<!-- Statistics Dashboard -->
	<div class="aanp-dashboard">
		<h2><?php esc_html_e( 'Statistics', 'newsforge-ai-auto-news-poster' ); ?></h2>
		<div class="aanp-stat-grid">
			<div class="aanp-stat-box aanp-stat-total">
				<h3><?php echo esc_html( $aanp_stats['total'] ); ?></h3>
				<p><?php esc_html_e( 'Total Posts', 'newsforge-ai-auto-news-poster' ); ?></p>
			</div>
			<div class="aanp-stat-box aanp-stat-today">
				<h3><?php echo esc_html( $aanp_stats['today'] ); ?></h3>
				<p><?php esc_html_e( 'Today', 'newsforge-ai-auto-news-poster' ); ?></p>
			</div>
			<div class="aanp-stat-box aanp-stat-week">
				<h3><?php echo esc_html( $aanp_stats['week'] ); ?></h3>
				<p><?php esc_html_e( 'This Week', 'newsforge-ai-auto-news-poster' ); ?></p>
			</div>
			<div class="aanp-stat-box aanp-stat-month">
				<h3><?php echo esc_html( $aanp_stats['month'] ); ?></h3>
				<p><?php esc_html_e( 'This Month', 'newsforge-ai-auto-news-poster' ); ?></p>
			</div>
		</div>
	</div>

	<!-- Generate Posts Section -->
	<div class="aanp-generate-section">
		<h2><?php esc_html_e( 'Generate Posts', 'newsforge-ai-auto-news-poster' ); ?></h2>
		<p>
			<?php esc_html_e( 'Click the button below to fetch the latest news and generate 5 unique blog posts automatically.', 'newsforge-ai-auto-news-poster' ); ?>
		</p>

		<div class="aanp-generate-controls">
			<button type="button" id="aanp-generate-posts" class="button button-primary button-large">
				<span class="dashicons dashicons-update aanp-btn-icon"></span>
				<?php esc_html_e( 'Generate 5 Posts', 'newsforge-ai-auto-news-poster' ); ?>
			</button>

			<div id="aanp-generation-status">
				<div class="aanp-progress">
					<div class="aanp-progress-bar"></div>
				</div>
				<p id="aanp-status-text"></p>
			</div>
		</div>

		<div id="aanp-generation-results">
			<h3><?php esc_html_e( 'Generated Posts', 'newsforge-ai-auto-news-poster' ); ?></h3>
			<div id="aanp-results-list"></div>
		</div>
	</div>

	<!-- Recent Posts -->
	<?php if ( ! empty( $aanp_recent_posts ) ) : ?>
	<div class="aanp-recent-posts">
		<h2><?php esc_html_e( 'Recent Generated Posts', 'newsforge-ai-auto-news-poster' ); ?></h2>
		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Title', 'newsforge-ai-auto-news-poster' ); ?></th>
					<th><?php esc_html_e( 'Status', 'newsforge-ai-auto-news-poster' ); ?></th>
					<th><?php esc_html_e( 'Generated', 'newsforge-ai-auto-news-poster' ); ?></th>
					<th><?php esc_html_e( 'Actions', 'newsforge-ai-auto-news-poster' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $aanp_recent_posts as $aanp_generated_post ) : ?>
				<tr>
					<td>
						<strong><?php echo esc_html( $aanp_generated_post['title'] ); ?></strong>
						<br>
						<small>
							<a href="<?php echo esc_url( $aanp_generated_post['source_url'] ); ?>" target="_blank" rel="noopener">
								<?php esc_html_e( 'Source', 'newsforge-ai-auto-news-poster' ); ?>
							</a>
						</small>
					</td>
					<td>
						<span class="post-status <?php echo esc_attr( $aanp_generated_post['status'] ); ?>">
							<?php echo esc_html( ucfirst( $aanp_generated_post['status'] ) ); ?>
						</span>
					</td>
					<td>
						<?php
						/* translators: human-readable time difference, e.g. "5 minutes ago" */
						echo esc_html(
							human_time_diff( strtotime( $aanp_generated_post['generated_at'] ), time() )
							. ' '
							. __( 'ago', 'newsforge-ai-auto-news-poster' )
						);
						?>
					</td>
					<td>
						<a href="<?php echo esc_url( $aanp_generated_post['edit_link'] ); ?>" class="button button-small">
							<?php esc_html_e( 'Edit', 'newsforge-ai-auto-news-poster' ); ?>
						</a>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php endif; ?>

	<!-- Settings Form -->
	<form method="post" action="options.php">
		<?php
		settings_fields( 'aanp_settings_group' );
		do_settings_sections( 'newsforge-ai-auto-news-poster' );
		?>

		<!-- Coming Soon: SEO meta optimization -->
		<div class="aanp-pro-features">
			<h2><?php esc_html_e( 'Coming Soon', 'newsforge-ai-auto-news-poster' ); ?></h2>
			<table class="form-table">
				<tr>
					<th scope="row"><?php esc_html_e( 'SEO Optimization', 'newsforge-ai-auto-news-poster' ); ?></th>
					<td>
						<label>
							<input type="checkbox" disabled />
							<?php esc_html_e( 'Auto-fill SEO meta tags (coming soon)', 'newsforge-ai-auto-news-poster' ); ?>
						</label>
						<p class="description">
							<?php esc_html_e( 'Automatically generate SEO-optimized meta descriptions and keywords.', 'newsforge-ai-auto-news-poster' ); ?>
						</p>
					</td>
				</tr>
			</table>
		</div>

		<?php submit_button(); ?>
	</form>
</div>
