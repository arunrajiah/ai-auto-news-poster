<?php
/**
 * Admin Settings Class
 *
 * @package AI_Auto_News_Poster
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class AANP_Admin_Settings {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'init_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_ajax_aanp_generate_posts', array($this, 'ajax_generate_posts'));
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_options_page(
            __('AI Auto News Poster', 'ai-auto-news-poster'),
            __('AI Auto News Poster', 'ai-auto-news-poster'),
            'manage_options',
            'ai-auto-news-poster',
            array($this, 'settings_page')
        );
    }
    
    /**
     * Initialize settings
     */
    public function init_settings() {
        register_setting('aanp_settings_group', 'aanp_settings', array($this, 'sanitize_settings'));
        
        // Main settings section
        add_settings_section(
            'aanp_main_section',
            __('Main Settings', 'ai-auto-news-poster'),
            array($this, 'main_section_callback'),
            'ai-auto-news-poster'
        );
        
        // LLM Provider field
        add_settings_field(
            'llm_provider',
            __('LLM Provider', 'ai-auto-news-poster'),
            array($this, 'llm_provider_callback'),
            'ai-auto-news-poster',
            'aanp_main_section'
        );
        
        // API Key field
        add_settings_field(
            'api_key',
            __('API Key', 'ai-auto-news-poster'),
            array($this, 'api_key_callback'),
            'ai-auto-news-poster',
            'aanp_main_section'
        );
        
        // Categories field
        add_settings_field(
            'categories',
            __('Post Categories', 'ai-auto-news-poster'),
            array($this, 'categories_callback'),
            'ai-auto-news-poster',
            'aanp_main_section'
        );
        
        // Word count field
        add_settings_field(
            'word_count',
            __('Word Count', 'ai-auto-news-poster'),
            array($this, 'word_count_callback'),
            'ai-auto-news-poster',
            'aanp_main_section'
        );
        
        // Tone field
        add_settings_field(
            'tone',
            __('Tone of Voice', 'ai-auto-news-poster'),
            array($this, 'tone_callback'),
            'ai-auto-news-poster',
            'aanp_main_section'
        );
        
        // RSS Feeds section
        add_settings_section(
            'aanp_rss_section',
            __('RSS Feeds', 'ai-auto-news-poster'),
            array($this, 'rss_section_callback'),
            'ai-auto-news-poster'
        );
        
        // RSS Feeds field
        add_settings_field(
            'rss_feeds',
            __('RSS Feed URLs', 'ai-auto-news-poster'),
            array($this, 'rss_feeds_callback'),
            'ai-auto-news-poster',
            'aanp_rss_section'
        );
    }
    
    /**
     * Enqueue admin scripts
     */
    public function enqueue_admin_scripts($hook) {
        if ($hook !== 'settings_page_ai-auto-news-poster') {
            return;
        }
        
        wp_enqueue_script(
            'aanp-admin-js',
            AANP_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            AANP_VERSION,
            true
        );
        
        wp_enqueue_style(
            'aanp-admin-css',
            AANP_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            AANP_VERSION
        );
        
        wp_localize_script('aanp-admin-js', 'aanp_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('aanp_nonce'),
            'generating_text' => __('Generating posts...', 'ai-auto-news-poster'),
            'success_text' => __('Posts generated successfully!', 'ai-auto-news-poster'),
            'error_text' => __('Error generating posts. Please try again.', 'ai-auto-news-poster')
        ));
    }
    
    /**
     * Settings page
     */
    public function settings_page() {
        include AANP_PLUGIN_DIR . 'admin/settings-page.php';
    }
    
    /**
     * Main section callback
     */
    public function main_section_callback() {
        echo '<p>' . __('Configure your AI Auto News Poster settings below.', 'ai-auto-news-poster') . '</p>';
    }
    
    /**
     * RSS section callback
     */
    public function rss_section_callback() {
        echo '<p>' . __('Manage RSS feeds for news sources.', 'ai-auto-news-poster') . '</p>';
    }
    
    /**
     * LLM Provider callback
     */
    public function llm_provider_callback() {
        $options = get_option('aanp_settings', array());
        $value = isset($options['llm_provider']) ? $options['llm_provider'] : 'openai';
        
        echo '<select name="aanp_settings[llm_provider]" id="llm_provider">';
        echo '<option value="openai"' . selected($value, 'openai', false) . '>OpenAI</option>';
        echo '<option value="anthropic"' . selected($value, 'anthropic', false) . '>Anthropic</option>';
        echo '<option value="custom"' . selected($value, 'custom', false) . '>Custom API</option>';
        echo '</select>';
        echo '<p class="description">' . __('Select your preferred LLM provider.', 'ai-auto-news-poster') . '</p>';
    }
    
    /**
     * API Key callback
     */
    public function api_key_callback() {
        $options = get_option('aanp_settings', array());
        $value = isset($options['api_key']) ? $options['api_key'] : '';
        
        echo '<input type="password" name="aanp_settings[api_key]" id="api_key" value="' . esc_attr($value) . '" class="regular-text" />';
        echo '<p class="description">' . __('Enter your API key for the selected LLM provider.', 'ai-auto-news-poster') . '</p>';
    }
    
    /**
     * Categories callback
     */
    public function categories_callback() {
        $options = get_option('aanp_settings', array());
        $selected_categories = isset($options['categories']) ? $options['categories'] : array();
        
        $categories = get_categories(array('hide_empty' => false));
        
        echo '<div class="aanp-categories">';
        foreach ($categories as $category) {
            $checked = in_array($category->term_id, $selected_categories) ? 'checked' : '';
            echo '<label>';
            echo '<input type="checkbox" name="aanp_settings[categories][]" value="' . $category->term_id . '" ' . $checked . ' />';
            echo ' ' . esc_html($category->name);
            echo '</label><br>';
        }
        echo '</div>';
        echo '<p class="description">' . __('Select categories for generated posts.', 'ai-auto-news-poster') . '</p>';
    }
    
    /**
     * Word count callback
     */
    public function word_count_callback() {
        $options = get_option('aanp_settings', array());
        $value = isset($options['word_count']) ? $options['word_count'] : 'medium';
        
        echo '<select name="aanp_settings[word_count]" id="word_count">';
        echo '<option value="short"' . selected($value, 'short', false) . '>Short (300-400 words)</option>';
        echo '<option value="medium"' . selected($value, 'medium', false) . '>Medium (500-600 words)</option>';
        echo '<option value="long"' . selected($value, 'long', false) . '>Long (800-1000 words)</option>';
        echo '</select>';
        echo '<p class="description">' . __('Select the desired word count for generated posts.', 'ai-auto-news-poster') . '</p>';
    }
    
    /**
     * Tone callback
     */
    public function tone_callback() {
        $options = get_option('aanp_settings', array());
        $value = isset($options['tone']) ? $options['tone'] : 'neutral';
        
        echo '<select name="aanp_settings[tone]" id="tone">';
        echo '<option value="neutral"' . selected($value, 'neutral', false) . '>Neutral</option>';
        echo '<option value="professional"' . selected($value, 'professional', false) . '>Professional</option>';
        echo '<option value="friendly"' . selected($value, 'friendly', false) . '>Friendly</option>';
        echo '</select>';
        echo '<p class="description">' . __('Select the tone of voice for generated content.', 'ai-auto-news-poster') . '</p>';
    }
    
    /**
     * RSS Feeds callback
     */
    public function rss_feeds_callback() {
        $options = get_option('aanp_settings', array());
        $feeds = isset($options['rss_feeds']) ? $options['rss_feeds'] : array();
        
        echo '<div id="rss-feeds-container">';
        if (!empty($feeds)) {
            foreach ($feeds as $index => $feed) {
                echo '<div class="rss-feed-row">';
                echo '<input type="url" name="aanp_settings[rss_feeds][]" value="' . esc_attr($feed) . '" class="regular-text" placeholder="https://example.com/feed.xml" />';
                echo '<button type="button" class="button remove-feed">Remove</button>';
                echo '</div>';
            }
        }
        echo '</div>';
        echo '<button type="button" id="add-feed" class="button">Add RSS Feed</button>';
        echo '<p class="description">' . __('Add RSS feed URLs for news sources.', 'ai-auto-news-poster') . '</p>';
    }
    
    /**
     * AJAX handler for generating posts
     */
    public function ajax_generate_posts() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'aanp_nonce')) {
            wp_die('Security check failed');
        }
        
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        try {
            // Initialize classes
            $news_fetch = new AANP_News_Fetch();
            $ai_generator = new AANP_AI_Generator();
            $post_creator = new AANP_Post_Creator();
            
            // Fetch news articles
            $articles = $news_fetch->fetch_latest_news();
            
            if (empty($articles)) {
                wp_send_json_error('No articles found');
                return;
            }
            
            // Limit to 5 posts for free version
            $articles = array_slice($articles, 0, 5);
            
            $generated_posts = array();
            
            foreach ($articles as $article) {
                // Generate content using AI
                $generated_content = $ai_generator->generate_content($article);
                
                if ($generated_content) {
                    // Create WordPress post
                    $post_id = $post_creator->create_post($generated_content, $article);
                    
                    if ($post_id) {
                        $generated_posts[] = array(
                            'id' => $post_id,
                            'title' => $generated_content['title'],
                            'edit_link' => get_edit_post_link($post_id)
                        );
                    }
                }
            }
            
            if (!empty($generated_posts)) {
                wp_send_json_success(array(
                    'message' => sprintf(__('%d posts generated successfully!', 'ai-auto-news-poster'), count($generated_posts)),
                    'posts' => $generated_posts
                ));
            } else {
                wp_send_json_error('Failed to generate posts');
            }
            
        } catch (Exception $e) {
            wp_send_json_error('Error: ' . $e->getMessage());
        }
    }
    
    /**
     * Sanitize settings
     */
    public function sanitize_settings($input) {
        $sanitized = array();
        
        // Validate and sanitize LLM provider
        if (isset($input['llm_provider'])) {
            $allowed_providers = array('openai', 'anthropic', 'custom');
            $provider = sanitize_text_field($input['llm_provider']);
            if (in_array($provider, $allowed_providers)) {
                $sanitized['llm_provider'] = $provider;
            } else {
                add_settings_error('aanp_settings', 'invalid_provider', __('Invalid LLM provider selected.', 'ai-auto-news-poster'));
                $sanitized['llm_provider'] = 'openai'; // Default fallback
            }
        }
        
        // Sanitize and encrypt API key
        if (isset($input['api_key'])) {
            $api_key = sanitize_text_field($input['api_key']);
            if (!empty($api_key)) {
                // Basic validation for API key format
                if (strlen($api_key) < 10) {
                    add_settings_error('aanp_settings', 'invalid_api_key', __('API key appears to be too short.', 'ai-auto-news-poster'));
                }
                // Store encrypted API key
                $sanitized['api_key'] = $this->encrypt_api_key($api_key);
            } else {
                $sanitized['api_key'] = '';
            }
        }
        
        // Validate and sanitize categories
        if (isset($input['categories']) && is_array($input['categories'])) {
            $sanitized['categories'] = array();
            $valid_categories = get_categories(array('hide_empty' => false));
            $valid_cat_ids = wp_list_pluck($valid_categories, 'term_id');
            
            foreach ($input['categories'] as $cat_id) {
                $cat_id = intval($cat_id);
                if (in_array($cat_id, $valid_cat_ids)) {
                    $sanitized['categories'][] = $cat_id;
                }
            }
        }
        
        // Validate and sanitize word count
        if (isset($input['word_count'])) {
            $allowed_counts = array('short', 'medium', 'long');
            $word_count = sanitize_text_field($input['word_count']);
            if (in_array($word_count, $allowed_counts)) {
                $sanitized['word_count'] = $word_count;
            } else {
                $sanitized['word_count'] = 'medium'; // Default fallback
            }
        }
        
        // Validate and sanitize tone
        if (isset($input['tone'])) {
            $allowed_tones = array('neutral', 'professional', 'friendly');
            $tone = sanitize_text_field($input['tone']);
            if (in_array($tone, $allowed_tones)) {
                $sanitized['tone'] = $tone;
            } else {
                $sanitized['tone'] = 'neutral'; // Default fallback
            }
        }
        
        // Validate and sanitize RSS feeds
        if (isset($input['rss_feeds']) && is_array($input['rss_feeds'])) {
            $sanitized['rss_feeds'] = array();
            $max_feeds = 20; // Limit number of feeds
            $feed_count = 0;
            
            foreach ($input['rss_feeds'] as $feed) {
                if ($feed_count >= $max_feeds) {
                    add_settings_error('aanp_settings', 'too_many_feeds', __('Maximum 20 RSS feeds allowed.', 'ai-auto-news-poster'));
                    break;
                }
                
                $feed = esc_url_raw($feed);
                if (!empty($feed) && filter_var($feed, FILTER_VALIDATE_URL)) {
                    // Additional security check for feed URL
                    $parsed_url = parse_url($feed);
                    if (isset($parsed_url['scheme']) && in_array($parsed_url['scheme'], array('http', 'https'))) {
                        $sanitized['rss_feeds'][] = $feed;
                        $feed_count++;
                    }
                }
            }
            
            // Ensure at least one feed exists
            if (empty($sanitized['rss_feeds'])) {
                $sanitized['rss_feeds'] = array(
                    'https://feeds.bbci.co.uk/news/rss.xml'
                );
                add_settings_error('aanp_settings', 'no_feeds', __('At least one RSS feed is required. Default feed added.', 'ai-auto-news-poster'));
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Encrypt API key for secure storage
     */
    private function encrypt_api_key($api_key) {
        if (function_exists('openssl_encrypt')) {
            $key = wp_salt('auth');
            $iv = openssl_random_pseudo_bytes(16);
            $encrypted = openssl_encrypt($api_key, 'AES-256-CBC', $key, 0, $iv);
            return base64_encode($iv . $encrypted);
        }
        // Fallback to base64 encoding (not secure but better than plain text)
        return base64_encode($api_key);
    }
    
    /**
     * Decrypt API key for use
     */
    public static function decrypt_api_key($encrypted_key) {
        if (empty($encrypted_key)) {
            return '';
        }
        
        if (function_exists('openssl_decrypt')) {
            $key = wp_salt('auth');
            $data = base64_decode($encrypted_key);
            $iv = substr($data, 0, 16);
            $encrypted = substr($data, 16);
            return openssl_decrypt($encrypted, 'AES-256-CBC', $key, 0, $iv);
        }
        // Fallback from base64 encoding
        return base64_decode($encrypted_key);
    }
}
