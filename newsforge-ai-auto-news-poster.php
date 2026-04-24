<?php
/**
 * Plugin Name: NewsForge – AI Auto News Poster
 * Plugin URI: https://github.com/arunrajiah/newsforge-ai-auto-news-poster
 * Description: Auto-generate blog posts from the latest news using AI. Supports manual and automatic WP-Cron scheduling with optional DALL-E 3 featured image generation.
 * Version: 1.0.7
 * Author: Arun Rajiah
 * Author URI: https://github.com/arunrajiah
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: newsforge-ai-auto-news-poster
 *
 * Requires at least: 5.1
 * Tested up to: 6.9
 * Requires PHP: 7.4
 *
 * @package AANP_Plugin
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants.
define( 'AANP_VERSION', '1.0.7' );
define( 'AANP_DB_VERSION', '1.1' );
define( 'AANP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'AANP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'AANP_PLUGIN_FILE', __FILE__ );

// Single authoritative list of built-in RSS feeds used as the default value.
define(
	'AANP_DEFAULT_FEEDS',
	array(
		'https://feeds.bbci.co.uk/news/rss.xml',
		'https://rss.cnn.com/rss/edition.rss',
		'https://feeds.reuters.com/reuters/topNews',
	)
);

/**
 * Main plugin class
 */
class AANP_Plugin {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'init' ) );
		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
	}

	/**
	 * Initialize plugin
	 */
	public function init() {
		// Text domain is automatically loaded by WordPress 4.6+.

		// Load includes.
		$this->load_includes();

		// Run DB migrations if needed (e.g. plugin updated without re-activation).
		$this->maybe_run_migrations();

		// Register custom cron interval and the scheduled-generation handler.
		$scheduler = new AANP_Scheduler();
		add_filter( 'cron_schedules', array( $scheduler, 'register_cron_schedules' ) ); // phpcs:ignore WordPress.WP.CronInterval.ChangeDetected
		add_action( AANP_Scheduler::CRON_HOOK, array( 'AANP_Scheduler', 'run' ) );

		// Initialize admin.
		if ( is_admin() ) {
			$this->init_admin();
		}
	}

	/**
	 * Run database migrations when the stored DB version is behind the current one.
	 */
	private function maybe_run_migrations(): void {
		$installed_db_version = get_option( 'aanp_db_version', '0' );
		if ( version_compare( $installed_db_version, AANP_DB_VERSION, '<' ) ) {
			$this->create_tables();
			update_option( 'aanp_db_version', AANP_DB_VERSION );
		}
	}

	/**
	 * Load include files
	 */
	private function load_includes() {
		require_once AANP_PLUGIN_DIR . 'includes/class-admin-settings.php';
		require_once AANP_PLUGIN_DIR . 'includes/class-news-fetch.php';
		require_once AANP_PLUGIN_DIR . 'includes/class-ai-generator.php';
		require_once AANP_PLUGIN_DIR . 'includes/class-image-generator.php';
		require_once AANP_PLUGIN_DIR . 'includes/class-post-creator.php';
		require_once AANP_PLUGIN_DIR . 'includes/class-scheduler.php';
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
			// Check PHP version.
			if ( version_compare( PHP_VERSION, '7.4', '<' ) ) {
				deactivate_plugins( plugin_basename( __FILE__ ) );
				$php_version = PHP_VERSION;
				/* translators: %s: PHP version number */
				$message = sprintf( __( 'NewsForge requires PHP 7.4 or higher. Your current version is %s', 'newsforge-ai-auto-news-poster' ), $php_version );
				wp_die( esc_html( $message ) );
			}

			// Check WordPress version.
			if ( version_compare( get_bloginfo( 'version' ), '5.0', '<' ) ) {
				deactivate_plugins( plugin_basename( __FILE__ ) );
				wp_die( esc_html__( 'NewsForge requires WordPress 5.0 or higher.', 'newsforge-ai-auto-news-poster' ) );
			}

			// Check required functions.
			if ( ! function_exists( 'wp_remote_get' ) || ! function_exists( 'wp_remote_post' ) ) {
				deactivate_plugins( plugin_basename( __FILE__ ) );
				wp_die( esc_html__( 'NewsForge requires WordPress HTTP API functions.', 'newsforge-ai-auto-news-poster' ) );
			}

			// Set default options.
			$default_options = array(
				'llm_provider'    => 'openai',
				'api_key'         => '',
				'categories'      => array(),
				'word_count'      => 'medium',
				'tone'            => 'neutral',
				'rss_feeds'       => AANP_DEFAULT_FEEDS,
				'schedule'        => 'disabled',
				'featured_images' => false,
			);

			add_option( 'aanp_settings', $default_options );

			// Create database table and record current DB version.
			$this->create_tables();
			update_option( 'aanp_db_version', AANP_DB_VERSION );

			// Set activation flag.
			add_option( 'aanp_activation_redirect', true );

		} catch ( Exception $e ) {
			error_log( 'AANP Activation Error: ' . $e->getMessage() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			deactivate_plugins( plugin_basename( __FILE__ ) );
			$error_message = $e->getMessage();
			/* translators: %s: Error message */
			$activation_error_message = sprintf( __( 'Plugin activation failed: %s', 'newsforge-ai-auto-news-poster' ), $error_message );
			wp_die( esc_html( $activation_error_message ) );
		}
	}

	/**
	 * Plugin deactivation
	 */
	public function deactivate() {
		( new AANP_Scheduler() )->unschedule();
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

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * Check if a valid Pro license is active.
	 * Reads the cached validation result stored by AANP_Admin_Settings::sanitize_settings().
	 */
	public static function is_pro_active(): bool {
		$is_valid = (bool) get_option( 'aanp_license_valid', false );
		return apply_filters( 'aanp_is_pro_active', $is_valid );
	}

	/**
	 * Get Pro upgrade URL (placeholder)
	 */
	public static function get_pro_upgrade_url() {
		return apply_filters( 'aanp_pro_upgrade_url', 'https://github.com/arunrajiah/newsforge-ai-auto-news-poster-pro' );
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

/**
 * Redirect to settings page after activation.
 *
 * Hooked to admin_init. Reads a one-time flag set during plugin activation
 * and redirects the admin to the plugin settings page.
 */
function aanp_activation_redirect() {
	if ( get_option( 'aanp_activation_redirect', false ) ) {
		delete_option( 'aanp_activation_redirect' );
		if ( ! isset( $_GET['activate-multi'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			wp_safe_redirect( admin_url( 'options-general.php?page=newsforge-ai-auto-news-poster&aanp_activated=1' ) );
			exit;
		}
	}
}
add_action( 'admin_init', 'aanp_activation_redirect' );

// Initialize the plugin.
try {
	new AANP_Plugin();
} catch ( Exception $e ) {
	error_log( 'AANP Fatal Error: ' . $e->getMessage() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
	add_action(
		'admin_notices',
		function () use ( $e ) {
			echo '<div class="notice notice-error"><p>NewsForge Fatal Error: ' . esc_html( $e->getMessage() ) . '</p></div>';
		}
	);
}
