<?php
/**
 * Uninstall script for NT LLMs.txt Builder
 * 
 * This file is executed when the plugin is uninstalled.
 */

// Prevent direct access
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete plugin options
delete_option('ntllms_txt_builder_options');
delete_option('ntllms_txt_builder_last_generated');

// Delete cache data
delete_transient('ntllms_txt_builder_cache_data');
delete_transient('ntllms_txt_builder_full_cache_data');

// Clean up per-post meta
global $wpdb;
$wpdb->delete($wpdb->postmeta, array('meta_key' => '_ntllms_txt_builder_ignore_page'));
$wpdb->delete($wpdb->postmeta, array('meta_key' => '_ntllms_txt_builder_clear_cache'));

// Flush rewrite rules
flush_rewrite_rules(); 