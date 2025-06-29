<?php
/**
 * Cache functionality for LLMs.txt Generator
 * 
 * @package LLMs_TXT_Generator
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Cache class for LLMs.txt Generator
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
        return delete_transient(LLMS_TXT_CACHE_KEY);
    }
    
    /**
     * Get cached content
     */
    public function get_cached_content() {
        return get_transient(LLMS_TXT_CACHE_KEY);
    }
    
    /**
     * Set cached content
     */
    public function set_cached_content($content) {
        return set_transient(LLMS_TXT_CACHE_KEY, $content, LLMS_TXT_CACHE_DURATION);
    }
    
    /**
     * Check if content is cached
     */
    public function is_cached() {
        return get_transient(LLMS_TXT_CACHE_KEY) !== false;
    }
    
    /**
     * Get cache duration
     */
    public function get_cache_duration() {
        return LLMS_TXT_CACHE_DURATION;
    }
} 