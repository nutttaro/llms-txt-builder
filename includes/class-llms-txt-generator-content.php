<?php
/**
 * Content Generator for LLMs.txt
 * 
 * @package LLMs_TXT_Generator
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Content Generator class for LLMs.txt
 */
class LLMs_TXT_Generator_Content {
    
    /**
     * Constructor
     */
    public function __construct() {
        // Constructor can be empty for now
    }
    
    /**
     * Get LLMs.txt content
     */
    public function get_llms_txt_content() {
        // Check cache first
        $cached_content = get_transient(LLMS_TXT_CACHE_KEY);
        if ($cached_content !== false) {
            return $cached_content;
        }
        
        // Generate new content
        $content = $this->generate_llms_txt_content();
        
        // Cache the content
        set_transient(LLMS_TXT_CACHE_KEY, $content, LLMS_TXT_CACHE_DURATION);
        
        return $content;
    }
    
    /**
     * Generate LLMs.txt content
     */
    public function generate_llms_txt_content() {
        $options = get_option('llms_txt_options', array());
        $content = "# " . get_bloginfo('name') . "\n";
        $content .= "# Generated on: " . current_time('Y-m-d H:i:s') . "\n";
        $content .= "# URL: " . home_url() . "\n\n";
        
        $content .= "# Project Overview\n";
        $custom_text = isset($options['custom_overview_text']) ? $options['custom_overview_text'] : 'This is a WordPress website with the following content structure:';
        $content .= $custom_text . "\n\n";
        
        // Homepage
        $content .= "# Homepage\n";
        $content .= home_url() . "\n\n";
        
        // Pages
        if (isset($options['include_pages']) && $options['include_pages'] === '1') {
            $content .= "# Pages\n";
            $pages = get_pages(array('post_status' => 'publish'));
            foreach ($pages as $page) {
                // Skip pages that are marked to be ignored
                $ignore_page = get_post_meta($page->ID, '_llms_txt_ignore_page', true);
                if ($ignore_page === '1') {
                    continue;
                }
                $content .= get_permalink($page->ID) . "\n";
            }
            $content .= "\n";
        }
        
        // Post types
        if (isset($options['post_types']) && is_array($options['post_types'])) {
            foreach ($options['post_types'] as $post_type) {
                // Skip pages if they're handled separately
                if ($post_type === 'page' && isset($options['include_pages']) && $options['include_pages'] === '1') {
                    continue;
                }
                
                // Skip WooCommerce products if WooCommerce is active (handled in WooCommerce section)
                if ($post_type === 'product' && class_exists('WooCommerce')) {
                    continue;
                }
                
                $posts = get_posts(array(
                    'post_type' => $post_type,
                    'post_status' => 'publish',
                    'numberposts' => -1
                ));
                
                if (!empty($posts)) {
                    $post_type_obj = get_post_type_object($post_type);
                    $content .= "# " . $post_type_obj->labels->name . "\n";
                    foreach ($posts as $post) {
                        // Skip posts that are marked to be ignored
                        $ignore_page = get_post_meta($post->ID, '_llms_txt_ignore_page', true);
                        if ($ignore_page === '1') {
                            continue;
                        }
                        $content .= get_permalink($post->ID) . "\n";
                    }
                    $content .= "\n";
                }
            }
        }
        
        // Taxonomies
        if (isset($options['taxonomies']) && is_array($options['taxonomies'])) {
            foreach ($options['taxonomies'] as $taxonomy) {
                // Skip WooCommerce taxonomies if WooCommerce is active (handled in WooCommerce section)
                if (class_exists('WooCommerce') && in_array($taxonomy, array('product_cat', 'product_tag'))) {
                    continue;
                }
                
                $terms = get_terms(array(
                    'taxonomy' => $taxonomy,
                    'hide_empty' => false
                ));
                
                if (!empty($terms) && !is_wp_error($terms)) {
                    $taxonomy_obj = get_taxonomy($taxonomy);
                    $content .= "# " . $taxonomy_obj->labels->name . "\n";
                    foreach ($terms as $term) {
                        $content .= get_term_link($term) . "\n";
                    }
                    $content .= "\n";
                }
            }
        }
        
        // Archives
        if (isset($options['include_archives']) && $options['include_archives'] === '1') {
            // Check if this is a blog (posts page)
            $posts_page_id = get_option('page_for_posts');
            $is_blog = $posts_page_id > 0;
            
            if ($is_blog) {
                $content .= "# Blog\n";
                $content .= get_permalink($posts_page_id) . "\n";
            } else {
                $content .= "# Archives\n";
                $content .= get_post_type_archive_link('post') . "\n";
            }
            
            // WooCommerce shop page
            if (class_exists('WooCommerce')) {
                $shop_page_id = wc_get_page_id('shop');
                if ($shop_page_id > 0) {
                    $content .= get_permalink($shop_page_id) . "\n";
                }
            }
            $content .= "\n";
        }
        
        // Author pages
        if (isset($options['include_author_pages']) && $options['include_author_pages'] === '1') {
            $content .= "# Author Pages\n";
            $authors = get_users(array('has_published_posts' => true));
            foreach ($authors as $author) {
                $content .= get_author_posts_url($author->ID) . "\n";
            }
            $content .= "\n";
        }
        
        // WooCommerce specific
        if (class_exists('WooCommerce')) {
            $content .= "# WooCommerce\n";
            
            // Product categories
            $product_cats = get_terms(array(
                'taxonomy' => 'product_cat',
                'hide_empty' => false
            ));
            if (!empty($product_cats) && !is_wp_error($product_cats)) {
                $content .= "## Product Categories\n";
                foreach ($product_cats as $cat) {
                    $content .= get_term_link($cat) . "\n";
                }
                $content .= "\n";
            }
            
            // Product tags
            $product_tags = get_terms(array(
                'taxonomy' => 'product_tag',
                'hide_empty' => false
            ));
            if (!empty($product_tags) && !is_wp_error($product_tags)) {
                $content .= "## Product Tags\n";
                foreach ($product_tags as $tag) {
                    $content .= get_term_link($tag) . "\n";
                }
                $content .= "\n";
            }
            
            // Products
            $products = get_posts(array(
                'post_type' => 'product',
                'post_status' => 'publish',
                'numberposts' => -1
            ));
            if (!empty($products)) {
                $content .= "## Products\n";
                foreach ($products as $product) {
                    // Skip products that are marked to be ignored
                    $ignore_page = get_post_meta($product->ID, '_llms_txt_ignore_page', true);
                    if ($ignore_page === '1') {
                        continue;
                    }
                    $content .= get_permalink($product->ID) . "\n";
                }
                $content .= "\n";
            }
        }
        
        $content .= "# End of LLMs.txt\n";
        
        return $content;
    }
    
    /**
     * Generate LLMs.txt file
     */
    public function generate_llms_txt() {
        $content = $this->generate_llms_txt_content();
        
        // Clear cache and set new content
        $llms_txt = LLMs_TXT_Generator::get_instance();
        $llms_txt->cache->clear_cache();
        set_transient(LLMS_TXT_CACHE_KEY, $content, LLMS_TXT_CACHE_DURATION);
        
        return true;
    }
} 