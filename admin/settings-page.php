<?php
/**
 * Admin Settings Page Template
 *
 * @package AI_Auto_News_Poster
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$options = get_option('aanp_settings', array());
$post_creator = new AANP_Post_Creator();
$stats = $post_creator->get_stats();
$recent_posts = $post_creator->get_recent_posts(5);
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <?php settings_errors(); ?>
    
    <!-- Pro Features Banner -->
    <div class="notice notice-info">
        <p><strong><?php _e('🚀 Upgrade to Pro for Advanced Features!', 'ai-auto-news-poster'); ?></strong></p>
        <ul style="margin-left: 20px;">
            <li><?php _e('• Automated scheduling with WP-Cron', 'ai-auto-news-poster'); ?></li>
            <li><?php _e('• Generate up to 30 posts per batch', 'ai-auto-news-poster'); ?></li>
            <li><?php _e('• Automatic featured image generation', 'ai-auto-news-poster'); ?></li>
            <li><?php _e('• SEO meta tags auto-fill', 'ai-auto-news-poster'); ?></li>
            <li><?php _e('• Priority support', 'ai-auto-news-poster'); ?></li>
        </ul>
        <p>
            <a href="#" class="button button-primary" onclick="alert('Pro version coming soon!')"><?php _e('Upgrade to Pro', 'ai-auto-news-poster'); ?></a>
        </p>
    </div>
    
    <!-- Statistics Dashboard -->
    <div class="aanp-dashboard">
        <h2><?php _e('Statistics', 'ai-auto-news-poster'); ?></h2>
        <div class="aanp-stat-grid">
            <div class="aanp-stat-box aanp-stat-total">
                <h3><?php echo esc_html($stats['total']); ?></h3>
                <p><?php _e('Total Posts', 'ai-auto-news-poster'); ?></p>
            </div>
            <div class="aanp-stat-box aanp-stat-today">
                <h3><?php echo esc_html($stats['today']); ?></h3>
                <p><?php _e('Today', 'ai-auto-news-poster'); ?></p>
            </div>
            <div class="aanp-stat-box aanp-stat-week">
                <h3><?php echo esc_html($stats['week']); ?></h3>
                <p><?php _e('This Week', 'ai-auto-news-poster'); ?></p>
            </div>
            <div class="aanp-stat-box aanp-stat-month">
                <h3><?php echo esc_html($stats['month']); ?></h3>
                <p><?php _e('This Month', 'ai-auto-news-poster'); ?></p>
            </div>
        </div>
    </div>

    <!-- Generate Posts Section -->
    <div class="aanp-generate-section">
        <h2><?php _e('Generate Posts', 'ai-auto-news-poster'); ?></h2>
        <p><?php _e('Click the button below to fetch the latest news and generate 5 unique blog posts automatically.', 'ai-auto-news-poster'); ?></p>

        <div class="aanp-generate-controls">
            <button type="button" id="aanp-generate-posts" class="button button-primary button-large">
                <span class="dashicons dashicons-update aanp-btn-icon"></span>
                <?php _e('Generate 5 Posts', 'ai-auto-news-poster'); ?>
            </button>

            <div id="aanp-generation-status">
                <div class="aanp-progress">
                    <div class="aanp-progress-bar"></div>
                </div>
                <p id="aanp-status-text"></p>
            </div>
        </div>

        <div id="aanp-generation-results">
            <h3><?php _e('Generated Posts', 'ai-auto-news-poster'); ?></h3>
            <div id="aanp-results-list"></div>
        </div>
    </div>

    <!-- Recent Posts -->
    <?php if (!empty($recent_posts)): ?>
    <div class="aanp-recent-posts">
        <h2><?php _e('Recent Generated Posts', 'ai-auto-news-poster'); ?></h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php _e('Title', 'ai-auto-news-poster'); ?></th>
                    <th><?php _e('Status', 'ai-auto-news-poster'); ?></th>
                    <th><?php _e('Generated', 'ai-auto-news-poster'); ?></th>
                    <th><?php _e('Actions', 'ai-auto-news-poster'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent_posts as $post): ?>
                <tr>
                    <td>
                        <strong><?php echo esc_html($post['title']); ?></strong>
                        <br>
                        <small><a href="<?php echo esc_url($post['source_url']); ?>" target="_blank" rel="noopener"><?php _e('Source', 'ai-auto-news-poster'); ?></a></small>
                    </td>
                    <td>
                        <span class="post-status <?php echo esc_attr($post['status']); ?>">
                            <?php echo esc_html(ucfirst($post['status'])); ?>
                        </span>
                    </td>
                    <td><?php echo esc_html(human_time_diff(strtotime($post['generated_at']), current_time('timestamp')) . ' ago'); ?></td>
                    <td>
                        <a href="<?php echo esc_url($post['edit_link']); ?>" class="button button-small"><?php _e('Edit', 'ai-auto-news-poster'); ?></a>
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
        settings_fields('aanp_settings_group');
        do_settings_sections('ai-auto-news-poster');
        ?>
        
        <!-- Pro Features (Disabled) -->
        <div class="aanp-pro-features">
            <h2><?php _e('Pro Features (Coming Soon)', 'ai-auto-news-poster'); ?></h2>
            
            <table class="form-table">
                <tr>
                    <th scope="row"><?php _e('Scheduling', 'ai-auto-news-poster'); ?></th>
                    <td>
                        <select disabled>
                            <option><?php _e('Manual Only (Free)', 'ai-auto-news-poster'); ?></option>
                            <option><?php _e('Every Hour (Pro)', 'ai-auto-news-poster'); ?></option>
                            <option><?php _e('Every 6 Hours (Pro)', 'ai-auto-news-poster'); ?></option>
                            <option><?php _e('Daily (Pro)', 'ai-auto-news-poster'); ?></option>
                        </select>
                        <p class="description"><?php _e('Automatically generate posts on a schedule.', 'ai-auto-news-poster'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php _e('Batch Size', 'ai-auto-news-poster'); ?></th>
                    <td>
                        <select disabled>
                            <option><?php _e('5 Posts (Free)', 'ai-auto-news-poster'); ?></option>
                            <option><?php _e('10 Posts (Pro)', 'ai-auto-news-poster'); ?></option>
                            <option><?php _e('20 Posts (Pro)', 'ai-auto-news-poster'); ?></option>
                            <option><?php _e('30 Posts (Pro)', 'ai-auto-news-poster'); ?></option>
                        </select>
                        <p class="description"><?php _e('Number of posts to generate per batch.', 'ai-auto-news-poster'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php _e('Featured Images', 'ai-auto-news-poster'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" disabled />
                            <?php _e('Auto-generate featured images (Pro)', 'ai-auto-news-poster'); ?>
                        </label>
                        <p class="description"><?php _e('Automatically create relevant featured images for posts.', 'ai-auto-news-poster'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php _e('SEO Optimization', 'ai-auto-news-poster'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" disabled />
                            <?php _e('Auto-fill SEO meta tags (Pro)', 'ai-auto-news-poster'); ?>
                        </label>
                        <p class="description"><?php _e('Automatically generate SEO-optimized meta descriptions and keywords.', 'ai-auto-news-poster'); ?></p>
                    </td>
                </tr>
            </table>
        </div>
        
        <?php submit_button(); ?>
    </form>
</div>

