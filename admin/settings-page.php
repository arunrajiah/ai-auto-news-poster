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
    <div class="aanp-dashboard" style="margin: 20px 0;">
        <h2><?php _e('Statistics', 'ai-auto-news-poster'); ?></h2>
        <div style="display: flex; gap: 20px; margin-bottom: 20px;">
            <div class="aanp-stat-box" style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; border-radius: 4px; text-align: center; min-width: 120px;">
                <h3 style="margin: 0; font-size: 24px; color: #0073aa;"><?php echo esc_html($stats['total']); ?></h3>
                <p style="margin: 5px 0 0 0; color: #666;"><?php _e('Total Posts', 'ai-auto-news-poster'); ?></p>
            </div>
            <div class="aanp-stat-box" style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; border-radius: 4px; text-align: center; min-width: 120px;">
                <h3 style="margin: 0; font-size: 24px; color: #00a32a;"><?php echo esc_html($stats['today']); ?></h3>
                <p style="margin: 5px 0 0 0; color: #666;"><?php _e('Today', 'ai-auto-news-poster'); ?></p>
            </div>
            <div class="aanp-stat-box" style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; border-radius: 4px; text-align: center; min-width: 120px;">
                <h3 style="margin: 0; font-size: 24px; color: #ff6900;"><?php echo esc_html($stats['week']); ?></h3>
                <p style="margin: 5px 0 0 0; color: #666;"><?php _e('This Week', 'ai-auto-news-poster'); ?></p>
            </div>
            <div class="aanp-stat-box" style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; border-radius: 4px; text-align: center; min-width: 120px;">
                <h3 style="margin: 0; font-size: 24px; color: #8c8f94;"><?php echo esc_html($stats['month']); ?></h3>
                <p style="margin: 5px 0 0 0; color: #666;"><?php _e('This Month', 'ai-auto-news-poster'); ?></p>
            </div>
        </div>
    </div>
    
    <!-- Generate Posts Section -->
    <div class="aanp-generate-section" style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; border-radius: 4px; margin-bottom: 20px;">
        <h2><?php _e('Generate Posts', 'ai-auto-news-poster'); ?></h2>
        <p><?php _e('Click the button below to fetch the latest news and generate 5 unique blog posts automatically.', 'ai-auto-news-poster'); ?></p>
        
        <div class="aanp-generate-controls">
            <button type="button" id="aanp-generate-posts" class="button button-primary button-large">
                <span class="dashicons dashicons-update" style="margin-top: 3px;"></span>
                <?php _e('Generate 5 Posts', 'ai-auto-news-poster'); ?>
            </button>
            
            <div id="aanp-generation-status" style="margin-top: 15px; display: none;">
                <div class="aanp-progress" style="background: #f0f0f1; border-radius: 3px; height: 20px; overflow: hidden;">
                    <div class="aanp-progress-bar" style="background: #0073aa; height: 100%; width: 0%; transition: width 0.3s ease;"></div>
                </div>
                <p id="aanp-status-text" style="margin: 10px 0 0 0; font-style: italic;"></p>
            </div>
        </div>
        
        <div id="aanp-generation-results" style="margin-top: 20px; display: none;">
            <h3><?php _e('Generated Posts', 'ai-auto-news-poster'); ?></h3>
            <div id="aanp-results-list"></div>
        </div>
    </div>
    
    <!-- Recent Posts -->
    <?php if (!empty($recent_posts)): ?>
    <div class="aanp-recent-posts" style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; border-radius: 4px; margin-bottom: 20px;">
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
        <div class="aanp-pro-features" style="background: #f9f9f9; padding: 20px; border: 1px solid #ddd; border-radius: 4px; margin: 20px 0; opacity: 0.6;">
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

<style>
.aanp-categories label {
    display: inline-block;
    margin-right: 15px;
    margin-bottom: 5px;
}

.rss-feed-row {
    margin-bottom: 10px;
}

.rss-feed-row input[type="url"] {
    margin-right: 10px;
}

.post-status.draft {
    color: #b32d2e;
}

.post-status.publish {
    color: #00a32a;
}

.post-status.private {
    color: #ff6900;
}

.aanp-pro-features {
    position: relative;
}

.aanp-pro-features::before {
    content: "🔒 PRO";
    position: absolute;
    top: 10px;
    right: 15px;
    background: #ff6900;
    color: white;
    padding: 5px 10px;
    border-radius: 3px;
    font-size: 12px;
    font-weight: bold;
}
</style>
