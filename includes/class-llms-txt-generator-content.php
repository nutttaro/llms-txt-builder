<?php
/**
 * LLMs.txt Content Generator
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
        $cached_content = get_transient(NT_LLMS_TXT_BUILDER_CACHE_KEY);
        if ($cached_content !== false) {
            return $cached_content;
        }
        
        // Generate content
        $content = $this->generate_llms_txt_content();
        
        // Cache the content
        set_transient(NT_LLMS_TXT_BUILDER_CACHE_KEY, $content, NT_LLMS_TXT_BUILDER_CACHE_DURATION);
        
        return $content;
    }
    
    /**
     * Generate LLMs.txt content
     */
    public function generate_llms_txt_content() {
        $options = get_option('nt_llms_txt_builder_options', array());
        $content = '';

        // Add header with site name and generation info
        $site_name = get_bloginfo('name');
        $content .= "# " . $site_name . "\n";
        $content .= "# Generated on: " . current_time('Y-m-d H:i:s') . "\n";
        $content .= "# URL: " . home_url() . "\n\n";

        // Add Project Overview section
        if (!empty($options['overview_text'])) {
            $content .= trim($options['overview_text']) . "\n\n";
        } else {
            $content .= "This is a WordPress website with the following content structure:\n\n";
        }

        // Pages
        if (!empty($options['post_types']) && in_array('page', $options['post_types'])) {
            $pages = get_pages(array('sort_column' => 'menu_order,post_title'));
            $valid_pages = array();
            
            // Filter out ignored pages
            foreach ($pages as $page) {
                $ignore_page = get_post_meta($page->ID, '_nt_llms_txt_builder_ignore_page', true);
                if (!$ignore_page) {
                    $valid_pages[] = $page;
                }
            }
            
            // Only add section if there are valid pages
            if (!empty($valid_pages)) {
                $content .= "# Pages\n";
                foreach ($valid_pages as $page) {
                    $content .= get_permalink($page->ID) . "\n";
                }
                $content .= "\n";
            }
        }

        // Posts
        if (!empty($options['post_types']) && in_array('post', $options['post_types'])) {
            $posts = get_posts(array('numberposts' => -1, 'post_status' => 'publish'));
            $valid_posts = array();
            
            // Filter out ignored posts
            foreach ($posts as $post) {
                $ignore_page = get_post_meta($post->ID, '_nt_llms_txt_builder_ignore_page', true);
                if (!$ignore_page) {
                    $valid_posts[] = $post;
                }
            }
            
            // Only add section if there are valid posts
            if (!empty($valid_posts)) {
                $content .= "# Posts\n";
                foreach ($valid_posts as $post) {
                    $content .= get_permalink($post->ID) . "\n";
                }
                $content .= "\n";
            }
        }

        // Blog/Archives
        if (!empty($options['include_archives'])) {
            $posts_page = get_option('page_for_posts');
            if ($posts_page) {
                $content .= "# Blog\n";
                $content .= get_permalink($posts_page) . "\n\n";
            } else {
                $content .= "# Archives\n";
                $content .= get_bloginfo('url') . "/?p=1\n\n";
            }
        }

        // Authors
        if (!empty($options['include_author_pages'])) {
            $authors = get_users(array('has_published_posts' => true));
            if (!empty($authors)) {
                $content .= "# Authors\n";
                foreach ($authors as $author) {
                    $content .= get_author_posts_url($author->ID) . "\n";
                }
                $content .= "\n";
            }
        }

        // Categories
        if (!empty($options['taxonomies']) && in_array('category', $options['taxonomies'])) {
            $categories = get_categories(array('hide_empty' => true));
            if (!empty($categories)) {
                $content .= "# Categories\n";
                foreach ($categories as $category) {
                    $content .= get_category_link($category->term_id) . "\n";
                }
                $content .= "\n";
            }
        }

        // Tags
        if (!empty($options['taxonomies']) && in_array('post_tag', $options['taxonomies'])) {
            $tags = get_tags(array('hide_empty' => true));
            if (!empty($tags)) {
                $content .= "# Tags\n";
                foreach ($tags as $tag) {
                    $content .= get_tag_link($tag->term_id) . "\n";
                }
                $content .= "\n";
            }
        }

        // Custom post types
        if (!empty($options['post_types'])) {
            $custom_post_types = array_diff($options['post_types'], array('post', 'page'));
            if (!empty($custom_post_types)) {
                foreach ($custom_post_types as $post_type) {
                    // Skip WooCommerce products if WooCommerce is active (handled in WooCommerce section)
                    if (class_exists('WooCommerce') && $post_type === 'product') {
                        continue;
                    }
                    

                    
                    if (post_type_exists($post_type)) {
                        $posts = get_posts(array('post_type' => $post_type, 'numberposts' => -1, 'post_status' => 'publish'));
                        $valid_posts = array();
                        
                        // Filter out ignored posts
                        foreach ($posts as $post) {
                            $ignore_page = get_post_meta($post->ID, '_nt_llms_txt_builder_ignore_page', true);
                            if (!$ignore_page) {
                                $valid_posts[] = $post;
                            }
                        }
                        
                        // Only add section if there are valid posts
                        if (!empty($valid_posts)) {
                            $post_type_obj = get_post_type_object($post_type);
                            $content .= "# " . $post_type_obj->labels->name . "\n";
                            foreach ($valid_posts as $post) {
                                $content .= get_permalink($post->ID) . "\n";
                            }
                            $content .= "\n";
                        }
                    }
                }
            }
        }

        // Custom taxonomies
        if (!empty($options['taxonomies'])) {
            $custom_taxonomies = array_diff($options['taxonomies'], array('category', 'post_tag'));
            if (!empty($custom_taxonomies)) {
                foreach ($custom_taxonomies as $taxonomy) {
                    // Skip WooCommerce taxonomies if WooCommerce is active (handled in WooCommerce section)
                    if (class_exists('WooCommerce') && in_array($taxonomy, array('product_cat', 'product_tag'))) {
                        continue;
                    }
                    
                    if (taxonomy_exists($taxonomy)) {
                        $terms = get_terms(array('taxonomy' => $taxonomy, 'hide_empty' => true));
                        if (!empty($terms)) {
                            $taxonomy_obj = get_taxonomy($taxonomy);
                            $content .= "# " . $taxonomy_obj->labels->name . "\n";
                            foreach ($terms as $term) {
                                $content .= get_term_link($term) . "\n";
                            }
                            $content .= "\n";
                        }
                    }
                }
            }
        }

        // WooCommerce
        if (class_exists('WooCommerce')) {
            // Product categories
            if (taxonomy_exists('product_cat')) {
                $product_categories = get_terms(array('taxonomy' => 'product_cat', 'hide_empty' => true));
                if (!empty($product_categories)) {
                    $content .= "# Product Categories\n";
                    foreach ($product_categories as $category) {
                        $content .= get_term_link($category) . "\n";
                    }
                    $content .= "\n";
                }
            }
            // Products
            if (post_type_exists('product')) {
                $products = get_posts(array('post_type' => 'product', 'numberposts' => -1, 'post_status' => 'publish'));
                $valid_products = array();
                
                // Filter out ignored products
                foreach ($products as $product) {
                    $ignore_page = get_post_meta($product->ID, '_nt_llms_txt_builder_ignore_page', true);
                    if (!$ignore_page) {
                        $valid_products[] = $product;
                    }
                }
                
                // Only add section if there are valid products
                if (!empty($valid_products)) {
                    $content .= "# Products\n";
                    foreach ($valid_products as $product) {
                        $content .= get_permalink($product->ID) . "\n";
                    }
                    $content .= "\n";
                }
            }
        }

        // End marker
        $content .= "# End of LLMs.txt\n";

        return $content;
    }
    
    /**
     * Generate and save LLMs.txt
     */
    public function generate_llms_txt() {
        $content = $this->generate_llms_txt_content();
        
        // Clear cache and set new content
        $llms_txt = LLMs_TXT_Generator::get_instance();
        $llms_txt->cache->clear_cache();
        set_transient(NT_LLMS_TXT_BUILDER_CACHE_KEY, $content, NT_LLMS_TXT_BUILDER_CACHE_DURATION);
        
        return $content;
    }
} 