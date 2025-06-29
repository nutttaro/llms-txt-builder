<?php
/**
 * Cache management for LLMs.txt Generator
 *
 * @package LLMs_TXT_Generator
 * @since 1.0.0
 */

namespace NT\LLMSTXT;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Cache management class
 *
 * @since 1.0.0
 */
class LLMs_TXT_Cache {
    
    /**
     * Constructor
     */
    public function __construct() {
        // Constructor can be empty for now
    }
    
    /**
     * Clear cache
     */
    public function clear_cache() {
        return delete_transient(LLMS_TXT_BUILDER_CACHE_KEY);
    }
    
    /**
     * Get cached content
     */
    public function get_cached_content() {
        return get_transient(LLMS_TXT_BUILDER_CACHE_KEY);
    }
    
    /**
     * Set cached content
     */
    public function set_cached_content($content) {
        return set_transient(LLMS_TXT_BUILDER_CACHE_KEY, $content, LLMS_TXT_BUILDER_CACHE_DURATION);
    }
    
    /**
     * Check if cache exists
     */
    public function has_cache() {
        return get_transient(LLMS_TXT_BUILDER_CACHE_KEY) !== false;
    }
    
    /**
     * Get cache duration
     */
    public function get_cache_duration() {
        return LLMS_TXT_BUILDER_CACHE_DURATION;
    }
} 