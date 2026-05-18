<?php
/**
 * Main LLMs.txt Generator Class
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
 * Main LLMs.txt Generator Class
 */
class LLMs_TXT_Generator {

    /**
     * Plugin instance
     *
     * @var LLMs_TXT_Generator
     */
    private static $instance = null;

    /**
     * Admin class instance
     *
     * @var LLMs_TXT_Admin
     */
    public $admin;

    /**
     * Generator class instance
     *
     * @var LLMs_TXT_Generator_Content
     */
    public $generator;

    /**
     * Cache class instance
     *
     * @var LLMs_TXT_Cache
     */
    public $cache;

    /**
     * Meta Box class instance
     *
     * @var LLMs_TXT_Meta_Box
     */
    public $meta_box;

    /**
     * Get plugin instance
     *
     * @return LLMs_TXT_Generator
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->init_hooks();
        $this->load_dependencies();
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action('init', array($this, 'register_rewrite_rules'));
        add_filter('query_vars', array($this, 'register_query_vars'));
        add_action('template_redirect', array($this, 'handle_llms_txt_request'));

        add_action('transition_post_status', array($this, 'on_post_status_change'), 10, 3);
        add_action('created_term', array($this, 'on_term_change'), 10, 3);
        add_action('edited_term', array($this, 'on_term_change'), 10, 3);
        add_action('delete_term', array($this, 'on_term_change'), 10, 3);

        register_activation_hook(NT_LLMS_TXT_BUILDER_PLUGIN_FILE, array($this, 'activate'));
        register_deactivation_hook(NT_LLMS_TXT_BUILDER_PLUGIN_FILE, array($this, 'deactivate'));
    }

    /**
     * Load plugin dependencies
     */
    private function load_dependencies() {
        require_once NT_LLMS_TXT_BUILDER_PLUGIN_PATH . 'includes/class-llms-txt-admin.php';
        $this->admin = new LLMs_TXT_Admin();

        require_once NT_LLMS_TXT_BUILDER_PLUGIN_PATH . 'includes/class-llms-txt-generator-content.php';
        $this->generator = new LLMs_TXT_Generator_Content();

        require_once NT_LLMS_TXT_BUILDER_PLUGIN_PATH . 'includes/class-llms-txt-cache.php';
        $this->cache = new LLMs_TXT_Cache();

        require_once NT_LLMS_TXT_BUILDER_PLUGIN_PATH . 'includes/class-llms-txt-meta-box.php';
        $this->meta_box = new LLMs_TXT_Meta_Box();
    }

    /**
     * Register rewrite rules for /llms.txt and /llms-full.txt
     */
    public function register_rewrite_rules() {
        add_rewrite_rule('^llms\.txt$', 'index.php?ntllms_txt=standard', 'top');
        add_rewrite_rule('^llms-full\.txt$', 'index.php?ntllms_txt=full', 'top');
    }

    /**
     * Register custom query var
     *
     * @param array $vars Existing query vars.
     * @return array
     */
    public function register_query_vars($vars) {
        $vars[] = 'ntllms_txt';
        return $vars;
    }

    /**
     * Handle LLMs.txt request via template_redirect
     */
    public function handle_llms_txt_request() {
        $variant = get_query_var('ntllms_txt');
        if (!$variant) {
            return;
        }

        if ('full' === $variant) {
            $content = $this->generator->get_llms_full_txt_content();
        } else {
            $content = $this->generator->get_llms_txt_content();
        }

        header('Content-Type: text/plain; charset=utf-8');
        header('Cache-Control: public, max-age=' . NT_LLMS_TXT_BUILDER_CACHE_DURATION);
        echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- plain text endpoint, content built from escaped WordPress data
        exit;
    }

    /**
     * Clear cache when a public post type changes status
     *
     * @param string   $new_status New post status.
     * @param string   $old_status Old post status.
     * @param \WP_Post $post       Post object.
     */
    public function on_post_status_change($new_status, $old_status, $post) {
        if ($new_status === $old_status) {
            return;
        }

        $public_statuses = array('publish', 'trash');
        if (!in_array($new_status, $public_statuses, true) && !in_array($old_status, $public_statuses, true)) {
            return;
        }

        $options = get_option('ntllms_txt_builder_options', array());
        $tracked_types = isset($options['post_types']) ? $options['post_types'] : array();
        if (!in_array($post->post_type, $tracked_types, true)) {
            return;
        }

        $this->cache->clear_all_cache();
    }

    /**
     * Clear cache when a tracked taxonomy term changes
     *
     * @param int    $term_id  Term ID.
     * @param int    $tt_id    Term taxonomy ID.
     * @param string $taxonomy Taxonomy slug.
     */
    public function on_term_change($term_id, $tt_id, $taxonomy) {
        $options = get_option('ntllms_txt_builder_options', array());
        $tracked_taxonomies = isset($options['taxonomies']) ? $options['taxonomies'] : array();
        if (!in_array($taxonomy, $tracked_taxonomies, true)) {
            return;
        }

        $this->cache->clear_all_cache();
    }

    /**
     * Plugin activation
     */
    public function activate() {
        $default_options = array(
            'post_types' => array('post'),
            'taxonomies' => array('category', 'post_tag'),
            'include_archives' => '1',
            'include_author_pages' => '1'
        );

        add_option('ntllms_txt_builder_options', $default_options);

        $this->generator->generate_llms_txt();

        $this->register_rewrite_rules();
        flush_rewrite_rules();
    }

    /**
     * Plugin deactivation
     */
    public function deactivate() {
        $this->cache->clear_all_cache();
        flush_rewrite_rules();
    }
}
