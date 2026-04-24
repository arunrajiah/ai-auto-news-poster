<?php
/**
 * Plugin Uninstall Handler
 *
 * Fired when the plugin is uninstalled via the WordPress admin.
 * Removes all plugin data: options, scheduled hooks, and the custom DB table.
 *
 * @package AI_Auto_News_Poster
 */

// Only run when triggered by WordPress uninstall — abort otherwise.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

// Remove plugin options.
delete_option( 'aanp_settings' );
delete_option( 'aanp_db_version' );
delete_option( 'aanp_activation_redirect' );
delete_option( 'aanp_license_valid' );

// Clear any scheduled cron hooks.
wp_clear_scheduled_hook( 'aanp_scheduled_generation' );

// Drop the custom posts log table.
$table_name = $wpdb->prefix . 'aanp_generated_posts';
// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- table name is a safe prefixed constant.
$wpdb->query( "DROP TABLE IF EXISTS `{$table_name}`" );

// Remove any post meta created by the plugin.
$wpdb->delete(
	$wpdb->postmeta,
	array( 'meta_key' => '_aanp_source_url' ),
	array( '%s' )
);
$wpdb->delete(
	$wpdb->postmeta,
	array( 'meta_key' => '_aanp_generated' ),
	array( '%s' )
);
