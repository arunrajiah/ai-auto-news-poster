<?php
/**
 * PHPUnit bootstrap for AI Auto News Poster.
 *
 * Loads the WordPress test stubs so tests can run without a live WordPress
 * install. The stubs live in tests/stubs.php and mock only the WordPress
 * functions and constants the plugin actually calls.
 */

define('ABSPATH', __DIR__ . '/');
define('AANP_VERSION', '1.0.6');
define('AANP_DB_VERSION', '1.1');
define('AANP_PLUGIN_DIR', dirname(__DIR__) . '/');
define('AANP_PLUGIN_URL', 'https://example.com/wp-content/plugins/newsforge-ai-auto-news-poster/');
define('AANP_PLUGIN_FILE', dirname(__DIR__) . '/newsforge-ai-auto-news-poster.php');
define('AANP_DEFAULT_FEEDS', array(
    'https://feeds.bbci.co.uk/news/rss.xml',
    'https://rss.cnn.com/rss/edition.rss',
    'https://feeds.reuters.com/reuters/topNews',
));
if (!defined('OPENSSL_RAW_DATA')) {
    define('OPENSSL_RAW_DATA', 1);
}

require_once __DIR__ . '/stubs.php';

require_once AANP_PLUGIN_DIR . 'includes/class-admin-settings.php';
require_once AANP_PLUGIN_DIR . 'includes/class-news-fetch.php';
require_once AANP_PLUGIN_DIR . 'includes/class-ai-generator.php';
require_once AANP_PLUGIN_DIR . 'includes/class-post-creator.php';
require_once AANP_PLUGIN_DIR . 'includes/class-image-generator.php';
require_once AANP_PLUGIN_DIR . 'includes/class-scheduler.php';
