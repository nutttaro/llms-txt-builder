<?php
/**
 * Plugin Name: LLMs.txt for WordPress and WooCommerce
 * Plugin URI: https://wordpress.org/plugins/llms-txt-wordpress
 * Description: This plugin generates an LLMs.txt file that includes all links from the website, with support for WooCommerce, custom post types, and custom taxonomies.
 * Version: 1.0.0
 * Author: nutttaro
 * Author URI: https://nutttaro.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: llms-txt-wordpress
 * Domain Path: /languages
 * Requires at least: 6.0
 * Tested up to: 6.8
 * Requires PHP: 7.4
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('LLMS_TXT_PLUGIN_URL', plugin_dir_url(__FILE__));
define('LLMS_TXT_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('LLMS_TXT_PLUGIN_FILE', __FILE__);
define('LLMS_TXT_PLUGIN_VERSION', '1.0.0');
define('LLMS_TXT_CACHE_KEY', 'llms_txt_cache');
define('LLMS_TXT_CACHE_DURATION', DAY_IN_SECONDS);

/**
 * Main plugin initialization
 */
function llms_txt_init() {
    // Load the main plugin class
    require_once LLMS_TXT_PLUGIN_PATH . 'includes/class-llms-txt-generator.php';
    
    // Initialize the plugin
    return LLMs_TXT_Generator::get_instance();
}

// Initialize the plugin
add_action('plugins_loaded', 'llms_txt_init'); 