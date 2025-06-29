<?php
/**
 * Uninstall LLMs.txt Generator Plugin
 * 
 * This file is executed when the plugin is uninstalled.
 * It removes all plugin data from the database.
 */

// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete plugin options
delete_option('llms_txt_options');

// Clear any cached LLMs.txt content
delete_transient('llms_txt_cache');

// Flush rewrite rules
flush_rewrite_rules(); 