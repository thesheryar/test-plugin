<?php
/**
 * Smart Contact Form Plugin
 *
 * A production-ready WordPress plugin for handling form submissions with database storage.
 *
 * @package Smart_Contact_Form
 * @version 1.0.0
 * @author Sheryar Khan
 * @license GPL-2.0-or-later
 * @link https://example.com
 *
 * Plugin Name: Smart Contact Form
 * Plugin URI: https://example.com
 * Description: A production-ready contact form plugin with secure submission handling and admin dashboard
 * Version: 1.0.0
 * Author: Sheryar Khan
 * Author URI: https://example.com
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Domain Path: /languages
 * Text Domain: smart-contact-form
 * Requires at least: 6.4
 * Requires PHP: 8.0
 *
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants.
if ( ! defined( 'SMART_FORM_PLUGIN_VERSION' ) ) {
	define( 'SMART_FORM_PLUGIN_VERSION', '1.0.0' );
}

if ( ! defined( 'SMART_FORM_PLUGIN_PATH' ) ) {
	define( 'SMART_FORM_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'SMART_FORM_PLUGIN_URL' ) ) {
	define( 'SMART_FORM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'SMART_FORM_PLUGIN_FILE' ) ) {
	define( 'SMART_FORM_PLUGIN_FILE', __FILE__ );
}

if ( ! defined( 'SMART_FORM_PLUGIN_BASENAME' ) ) {
	define( 'SMART_FORM_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
}

/**
 * Load plugin text domain
 *
 * @since 1.0.0
 */
add_action( 'init', 'smart_form_load_textdomain' );

/**
 * Load text domain for translations
 *
 * @since 1.0.0
 */
function smart_form_load_textdomain() {
	load_plugin_textdomain(
		'smart-contact-form',
		false,
		dirname( SMART_FORM_PLUGIN_BASENAME ) . '/languages'
	);
}

/**
 * Load plugin class
 *
 * @since 1.0.0
 */
function smart_form_load_plugin() {
	// Include the main plugin class.
	require_once SMART_FORM_PLUGIN_PATH . 'includes/class-smart-form-handler.php';

	// Initialize the plugin.
	if ( class_exists( 'Smart_Form_Handler' ) ) {
		global $smart_form_handler;
		$smart_form_handler = new Smart_Form_Handler();
	}
}

// Load plugin on plugins_loaded hook.
add_action( 'plugins_loaded', 'smart_form_load_plugin' );

/**
 * Plugin activation hook
 *
 * @since 1.0.0
 */
register_activation_hook(
	SMART_FORM_PLUGIN_FILE,
	function() {
		// Require the class file for the static method.
		require_once SMART_FORM_PLUGIN_PATH . 'includes/class-smart-form-handler.php';

		// Create database table.
		if ( class_exists( 'Smart_Form_Handler' ) ) {
			Smart_Form_Handler::create_tables();
		}

		// Clear any cache.
		wp_cache_flush();
	}
);

/**
 * Plugin deactivation hook
 *
 * @since 1.0.0
 */
register_deactivation_hook(
	SMART_FORM_PLUGIN_FILE,
	function() {
		// Clear any cache.
		wp_cache_flush();
	}
);