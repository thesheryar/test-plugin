<?php
/**
 * Uninstall Smart Contact Form Plugin
 *
 * Handles plugin cleanup when uninstalled
 *
 * @package Smart_Contact_Form
 * @since 1.0.0
 */

// Exit if accessed directly or not called by WordPress.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

// Define table name.
$table_name = $wpdb->prefix . 'smart_form_submissions';

// Drop table if exists.
$wpdb->query( $wpdb->prepare( 'DROP TABLE IF EXISTS %i', $table_name ) );

// Delete plugin options.
delete_option( 'smart_form_plugin_version' );
delete_option( 'smart_form_settings' );

// Clear cache.
wp_cache_flush();

// Note: We do not delete user data from other plugins or critical WordPress data.
// Only plugin-specific data is cleaned up.
