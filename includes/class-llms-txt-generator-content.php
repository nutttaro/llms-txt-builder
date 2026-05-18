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
     * Get LLMs.txt content (cached)
     */
    public function get_llms_txt_content() {
        $cached_content = get_transient(NT_LLMS_TXT_BUILDER_CACHE_KEY);
        if ($cached_content !== false) {
            return $cached_content;
        }

        $content = $this->build_content(false);
        set_transient(NT_LLMS_TXT_BUILDER_CACHE_KEY, $content, NT_LLMS_TXT_BUILDER_CACHE_DURATION);
        if (!get_option('ntllms_txt_builder_last_generated')) {
            update_option('ntllms_txt_builder_last_generated', time());
        }

        return $content;
    }

    /**
     * Get LLMs-full.txt content (cached)
     */
    public function get_llms_full_txt_content() {
        $cached_content = get_transient(NT_LLMS_TXT_BUILDER_FULL_CACHE_KEY);
        if ($cached_content !== false) {
            return $cached_content;
        }

        $content = $this->build_content(true);
        set_transient(NT_LLMS_TXT_BUILDER_FULL_CACHE_KEY, $content, NT_LLMS_TXT_BUILDER_CACHE_DURATION);

        return $content;
    }

    /**
     * Build the llms.txt content following the spec.
     *
     * @param bool $full Whether to include excerpts/descriptions (llms-full.txt).
     * @return string
     */
    private function build_content($full = false) {
        $options = get_option('ntllms_txt_builder_options', array());
        $content = '';

        $site_name = get_bloginfo('name');
        $content .= "# " . $site_name . "\n\n";

        if (!empty($options['overview_text'])) {
            $content .= "> " . trim($options['overview_text']) . "\n\n";
        } else {
            $site_desc = get_bloginfo('description');
            if ($site_desc) {
                $content .= "> " . $site_desc . "\n\n";
            }
        }

        $content .= $this->build_pages_section($options, $full);
        $content .= $this->build_posts_section($options, $full);
        $content .= $this->build_archives_section($options);
        $content .= $this->build_authors_section($options);
        $content .= $this->build_categories_section($options);
        $content .= $this->build_tags_section($options);
        $content .= $this->build_custom_post_types_section($options, $full);
        $content .= $this->build_custom_taxonomies_section($options);
        $content .= $this->build_woocommerce_section($options, $full);

        return $content;
    }

    /**
     * Format a single link entry.
     *
     * @param string $title Title text.
     * @param string $url   Permalink.
     * @param string $desc  Description/excerpt (empty string to omit).
     * @return string
     */
    private function format_link($title, $url, $desc = '') {
        $line = "- [" . $title . "](" . $url . ")";
        if ($desc) {
            $line .= ": " . $desc;
        }
        return $line . "\n";
    }

    /**
     * Get a trimmed plain-text excerpt for a post.
     *
     * @param \WP_Post $post Post object.
     * @param bool     $full Whether to include excerpt.
     * @return string
     */
    private function get_post_description($post, $full) {
        if (!$full) {
            return '';
        }

        $excerpt = $post->post_excerpt;
        if (empty($excerpt)) {
            $excerpt = wp_trim_words(wp_strip_all_tags($post->post_content), 30, '...');
        }

        return $excerpt;
    }

    /**
     * Filter out posts with the ignore meta flag set.
     *
     * @param array $posts Array of WP_Post objects.
     * @return array
     */
    private function filter_ignored($posts) {
        return array_filter($posts, function ($post) {
            return !get_post_meta($post->ID, '_ntllms_txt_builder_ignore_page', true);
        });
    }

    // ------------------------------------------------------------------
    // Section builders
    // ------------------------------------------------------------------

    private function build_pages_section($options, $full) {
        if (empty($options['post_types']) || !in_array('page', $options['post_types'], true)) {
            return '';
        }

        $pages = get_pages(array('sort_column' => 'menu_order,post_title'));
        $pages = $this->filter_ignored($pages);
        if (empty($pages)) {
            return '';
        }

        $out = "## Pages\n\n";
        foreach ($pages as $page) {
            $out .= $this->format_link(
                get_the_title($page),
                get_permalink($page->ID),
                $this->get_post_description($page, $full)
            );
        }
        return $out . "\n";
    }

    private function build_posts_section($options, $full) {
        if (empty($options['post_types']) || !in_array('post', $options['post_types'], true)) {
            return '';
        }

        $posts = get_posts(array('numberposts' => -1, 'post_status' => 'publish'));
        $posts = $this->filter_ignored($posts);
        if (empty($posts)) {
            return '';
        }

        $out = "## Posts\n\n";
        foreach ($posts as $post) {
            $out .= $this->format_link(
                get_the_title($post),
                get_permalink($post->ID),
                $this->get_post_description($post, $full)
            );
        }
        return $out . "\n";
    }

    private function build_archives_section($options) {
        if (empty($options['include_archives'])) {
            return '';
        }

        $posts_page = get_option('page_for_posts');
        if ($posts_page) {
            $out = "## Blog\n\n";
            $out .= $this->format_link(get_the_title($posts_page), get_permalink($posts_page));
            return $out . "\n";
        }

        return '';
    }

    private function build_authors_section($options) {
        if (empty($options['include_author_pages'])) {
            return '';
        }

        $authors = get_users(array('has_published_posts' => true));
        if (empty($authors)) {
            return '';
        }

        $out = "## Authors\n\n";
        foreach ($authors as $author) {
            $out .= $this->format_link($author->display_name, get_author_posts_url($author->ID));
        }
        return $out . "\n";
    }

    private function build_categories_section($options) {
        if (empty($options['taxonomies']) || !in_array('category', $options['taxonomies'], true)) {
            return '';
        }

        $categories = get_categories(array('hide_empty' => true));
        if (empty($categories)) {
            return '';
        }

        $out = "## Categories\n\n";
        foreach ($categories as $category) {
            $out .= $this->format_link($category->name, get_category_link($category->term_id));
        }
        return $out . "\n";
    }

    private function build_tags_section($options) {
        if (empty($options['taxonomies']) || !in_array('post_tag', $options['taxonomies'], true)) {
            return '';
        }

        $tags = get_tags(array('hide_empty' => true));
        if (empty($tags)) {
            return '';
        }

        $out = "## Tags\n\n";
        foreach ($tags as $tag) {
            $out .= $this->format_link($tag->name, get_tag_link($tag->term_id));
        }
        return $out . "\n";
    }

    private function build_custom_post_types_section($options, $full) {
        if (empty($options['post_types'])) {
            return '';
        }

        $custom_post_types = array_diff($options['post_types'], array('post', 'page'));
        if (class_exists('WooCommerce')) {
            $custom_post_types = array_diff($custom_post_types, array('product'));
        }
        if (empty($custom_post_types)) {
            return '';
        }

        $out = '';
        foreach ($custom_post_types as $post_type) {
            if (!post_type_exists($post_type)) {
                continue;
            }

            $posts = get_posts(array('post_type' => $post_type, 'numberposts' => -1, 'post_status' => 'publish'));
            $posts = $this->filter_ignored($posts);
            if (empty($posts)) {
                continue;
            }

            $post_type_obj = get_post_type_object($post_type);
            $out .= "## " . $post_type_obj->labels->name . "\n\n";
            foreach ($posts as $post) {
                $out .= $this->format_link(
                    get_the_title($post),
                    get_permalink($post->ID),
                    $this->get_post_description($post, $full)
                );
            }
            $out .= "\n";
        }
        return $out;
    }

    private function build_custom_taxonomies_section($options) {
        if (empty($options['taxonomies'])) {
            return '';
        }

        $custom_taxonomies = array_diff($options['taxonomies'], array('category', 'post_tag'));
        if (class_exists('WooCommerce')) {
            $custom_taxonomies = array_diff($custom_taxonomies, array('product_cat', 'product_tag'));
        }
        if (empty($custom_taxonomies)) {
            return '';
        }

        $out = '';
        foreach ($custom_taxonomies as $taxonomy) {
            if (!taxonomy_exists($taxonomy)) {
                continue;
            }

            $terms = get_terms(array('taxonomy' => $taxonomy, 'hide_empty' => true));
            if (empty($terms) || is_wp_error($terms)) {
                continue;
            }

            $taxonomy_obj = get_taxonomy($taxonomy);
            $out .= "## " . $taxonomy_obj->labels->name . "\n\n";
            foreach ($terms as $term) {
                $out .= $this->format_link($term->name, get_term_link($term));
            }
            $out .= "\n";
        }
        return $out;
    }

    private function build_woocommerce_section($options, $full) {
        if (!class_exists('WooCommerce')) {
            return '';
        }

        $out = '';

        if (!empty($options['taxonomies']) && in_array('product_cat', $options['taxonomies'], true) && taxonomy_exists('product_cat')) {
            $product_categories = get_terms(array('taxonomy' => 'product_cat', 'hide_empty' => true));
            if (!empty($product_categories) && !is_wp_error($product_categories)) {
                $out .= "## Product Categories\n\n";
                foreach ($product_categories as $category) {
                    $out .= $this->format_link($category->name, get_term_link($category));
                }
                $out .= "\n";
            }
        }

        if (!empty($options['post_types']) && in_array('product', $options['post_types'], true) && post_type_exists('product')) {
            $products = get_posts(array('post_type' => 'product', 'numberposts' => -1, 'post_status' => 'publish'));
            $products = $this->filter_ignored($products);
            if (!empty($products)) {
                $out .= "## Products\n\n";
                foreach ($products as $product) {
                    $out .= $this->format_link(
                        get_the_title($product),
                        get_permalink($product->ID),
                        $this->get_post_description($product, $full)
                    );
                }
                $out .= "\n";
            }
        }

        return $out;
    }

    /**
     * Generate and save LLMs.txt (clears cache first)
     */
    public function generate_llms_txt() {
        $content = $this->build_content(false);

        $llms_txt = LLMs_TXT_Generator::get_instance();
        $llms_txt->cache->clear_all_cache();
        set_transient(NT_LLMS_TXT_BUILDER_CACHE_KEY, $content, NT_LLMS_TXT_BUILDER_CACHE_DURATION);
        update_option('ntllms_txt_builder_last_generated', time());

        return $content;
    }
}
