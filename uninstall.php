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

// Delete cache data
delete_transient('ntllms_txt_builder_cache_data');

// Clean up any other plugin data if needed
// Note: We don't delete the LLMs.txt file as it might be useful to keep

// Flush rewrite rules
flush_rewrite_rules(); 