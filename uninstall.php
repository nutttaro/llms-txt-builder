<?php
/**
 * Uninstall LLMs.txt Builder Plugin
 *
 * @package LLMs_TXT_Generator
 */

// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete plugin options
delete_option('llms_txt_builder_options');

// Clear any cached data
delete_transient('llms_txt_builder_cache');

// Flush rewrite rules
flush_rewrite_rules(); 