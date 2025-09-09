<?php
/**
 * Plugin Name: AI Auto News Poster
 * Plugin URI: https://github.com/arunrajiah/ai-auto-news-poster
 * Description: Auto-generate blog posts based on the latest news/content. Free users can generate up to 5 posts manually per batch.
 * Version: 1.0.6
 * Author: Arun Rajiah
 * Author URI: https://github.com/arunrajiah
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: ai-auto-news-poster

 * Requires at least: 5.0
 * Tested up to: 6.8
 * Requires PHP: 7.4
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('AANP_VERSION', '1.0.6');
define('AANP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('AANP_PLUGIN_URL', plugin_dir_url(__FILE__));
define('AANP_PLUGIN_FILE', __FILE__);

/**
 * Main plugin class
 */
class AI_Auto_News_Poster {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', array($this, 'init'));
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    /**
     * Initialize plugin
     */
    public function init() {
        // Text domain is automatically loaded by WordPress 4.6+
        
        // Load includes
        $this->load_includes();
        
        // Initialize admin
        if (is_admin()) {
            $this->init_admin();
        }
    }
    
    /**
     * Load include files
     */
    private function load_includes() {
        require_once AANP_PLUGIN_DIR . 'includes/class-admin-settings.php';
        require_once AANP_PLUGIN_DIR . 'includes/class-news-fetch.php';
        require_once AANP_PLUGIN_DIR . 'includes/class-ai-generator.php';
        require_once AANP_PLUGIN_DIR . 'includes/class-post-creator.php';
        require_once AANP_PLUGIN_DIR . 'includes/class-pro-features.php';
    }
    
    /**
     * Initialize admin functionality
     */
    private function init_admin() {
        new AANP_Admin_Settings();
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        try {
            // Check PHP version
            if (version_compare(PHP_VERSION, '7.4', '<')) {
                deactivate_plugins(plugin_basename(__FILE__));
                $php_version = PHP_VERSION;
                /* translators: %s: PHP version number */
                wp_die(sprintf(__('AI Auto News Poster requires PHP 7.4 or higher. Your current version is %s', 'ai-auto-news-poster'), $php_version));
            }
            
            // Check WordPress version
            if (version_compare(get_bloginfo('version'), '5.0', '<')) {
                deactivate_plugins(plugin_basename(__FILE__));
                wp_die(__('AI Auto News Poster requires WordPress 5.0 or higher.', 'ai-auto-news-poster'));
            }
            
            // Check required functions
            if (!function_exists('wp_remote_get') || !function_exists('wp_remote_post')) {
                deactivate_plugins(plugin_basename(__FILE__));
                wp_die(__('AI Auto News Poster requires WordPress HTTP API functions.', 'ai-auto-news-poster'));
            }
            
            // Set default options
            $default_options = array(
                'llm_provider' => 'openai',
                'api_key' => '',
                'categories' => array(),
                'word_count' => 'medium',
                'tone' => 'neutral',
                'rss_feeds' => array(
                    'https://feeds.bbci.co.uk/news/rss.xml',
                    'https://rss.cnn.com/rss/edition.rss',
                    'https://feeds.reuters.com/reuters/topNews'
                )
            );
            
            add_option('aanp_settings', $default_options);
            
            // Create database table if needed
            $this->create_tables();
            
            // Set activation flag
            add_option('aanp_activation_redirect', true);
            
        } catch (Exception $e) {
            error_log('AANP Activation Error: ' . $e->getMessage());
            deactivate_plugins(plugin_basename(__FILE__));
            $error_message = $e->getMessage();
            /* translators: %s: Error message */
            wp_die(sprintf(__('Plugin activation failed: %s', 'ai-auto-news-poster'), $error_message));
        }
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Clean up scheduled events if any
        wp_clear_scheduled_hook('aanp_scheduled_generation');
    }
    
    /**
     * Create database tables
     */
    private function create_tables() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'aanp_generated_posts';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            post_id bigint(20) NOT NULL,
            source_url varchar(255) NOT NULL,
            generated_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}

    /**
     * Check if Pro features are available (placeholder)
     */
    public static function is_pro_active() {
        // This will be used for Pro version detection
        return apply_filters('aanp_is_pro_active', false);
    }
    
    /**
     * Get Pro upgrade URL (placeholder)
     */
    public static function get_pro_upgrade_url() {
        return apply_filters('aanp_pro_upgrade_url', 'https://github.com/arunrajiah/ai-auto-news-poster-pro');
    }
    
    /**
     * Get maximum posts per batch based on version
     */
    public static function get_max_posts_per_batch() {
        return self::is_pro_active() ? 30 : 5;
    }
    
    /**
     * Check if scheduling is available
     */
    public static function is_scheduling_available() {
        return self::is_pro_active();
    }
    
    /**
     * Check if featured image generation is available
     */
    public static function is_featured_images_available() {
        return self::is_pro_active();
    }
    
    /**
     * Check if SEO features are available
     */
    public static function is_seo_features_available() {
        return self::is_pro_active();
    }
}

// Add activation redirect and error handling methods
if (!function_exists('aanp_activation_redirect')) {
    function aanp_activation_redirect() {
        if (get_option('aanp_activation_redirect', false)) {
            delete_option('aanp_activation_redirect');
            if (!isset($_GET['activate-multi'])) {
                wp_redirect(admin_url('options-general.php?page=ai-auto-news-poster&aanp_activated=1'));
                exit;
            }
        }
    }
}

// Initialize the plugin
try {
    new AI_Auto_News_Poster();
} catch (Exception $e) {
    error_log('AANP Fatal Error: ' . $e->getMessage());
    add_action('admin_notices', function() use ($e) {
        echo '<div class="notice notice-error"><p>AI Auto News Poster Fatal Error: ' . esc_html($e->getMessage()) . '</p></div>';
    });
}
