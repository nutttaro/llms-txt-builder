<?php
/**
 * Meta Box functionality for LLMs.txt Generator
 *
 * Provides per-post controls in both the block editor (PluginDocumentSettingPanel)
 * and the classic editor (traditional meta box).
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
 * Meta Box class for LLMs.txt Generator
 */
class LLMs_TXT_Meta_Box {

    /**
     * Constructor
     */
    public function __construct() {
        $this->init_hooks();
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action('init', array($this, 'register_meta'));
        add_action('add_meta_boxes', array($this, 'add_meta_box'));
        add_action('save_post', array($this, 'save_meta_box_data'));
        add_action('enqueue_block_editor_assets', array($this, 'enqueue_block_editor_assets'));
        add_action('rest_api_init', array($this, 'register_rest_hooks'));
    }

    /**
     * Register post meta for REST API / block editor access.
     */
    public function register_meta() {
        $meta_args = array(
            'show_in_rest'  => true,
            'single'        => true,
            'type'          => 'boolean',
            'default'       => false,
            'auth_callback' => function () {
                return current_user_can('edit_posts');
            },
        );

        register_post_meta('', '_ntllms_txt_builder_ignore_page', $meta_args);
        register_post_meta('', '_ntllms_txt_builder_clear_cache', $meta_args);
    }

    /**
     * Add classic meta box (hidden in block editor via __back_compat_meta_box).
     */
    public function add_meta_box() {
        $post_types = get_post_types(array('public' => true));

        foreach ($post_types as $post_type) {
            add_meta_box(
                'ntllms_txt_builder_cache_clear',
                esc_html__('LLMs.txt Builder', 'nt-llms-txt-builder'),
                array($this, 'meta_box_callback'),
                $post_type,
                'side',
                'default',
                array('__back_compat_meta_box' => true)
            );
        }
    }

    /**
     * Enqueue block editor sidebar panel script.
     */
    public function enqueue_block_editor_assets() {
        $screen = get_current_screen();
        if (!$screen || $screen->base !== 'post') {
            return;
        }

        $js_file = NT_LLMS_TXT_BUILDER_PLUGIN_PATH . 'assets/js/editor-sidebar.js';

        wp_enqueue_script(
            'nt-llms-txt-builder-editor-sidebar',
            plugins_url('assets/js/editor-sidebar.js', NT_LLMS_TXT_BUILDER_PLUGIN_FILE),
            array('wp-plugins', 'wp-edit-post', 'wp-element', 'wp-components', 'wp-data', 'wp-i18n'),
            file_exists($js_file) ? filemtime($js_file) : NT_LLMS_TXT_BUILDER_PLUGIN_VERSION,
            true
        );
    }

    /**
     * Meta box callback (classic editor only)
     */
    public function meta_box_callback($post) {
        wp_nonce_field('ntllms_txt_builder_meta_box_nonce', 'ntllms_txt_builder_meta_box_nonce');

        $clear_cache = get_post_meta($post->ID, '_ntllms_txt_builder_clear_cache', true);
        $ignore_page = get_post_meta($post->ID, '_ntllms_txt_builder_ignore_page', true);

        ?>
        <p>
            <label>
                <input type="checkbox" name="ntllms_txt_builder_clear_cache" value="1" <?php checked($clear_cache, '1'); ?> />
                <?php esc_html_e('Clear LLMs.txt cache on update', 'nt-llms-txt-builder'); ?>
            </label>
        </p>
        <p>
            <label>
                <input type="checkbox" name="ntllms_txt_builder_ignore_page" value="1" <?php checked($ignore_page, '1'); ?> />
                <?php esc_html_e('Ignore this page/post in LLMs.txt', 'nt-llms-txt-builder'); ?>
            </label>
        </p>
        <?php
    }

    /**
     * Save meta box data (classic editor path)
     */
    public function save_meta_box_data($post_id) {
        if (!isset($_POST['ntllms_txt_builder_meta_box_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['ntllms_txt_builder_meta_box_nonce'])), 'ntllms_txt_builder_meta_box_nonce')) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        $clear_cache = isset($_POST['ntllms_txt_builder_clear_cache']) ? '1' : '0';
        $ignore_page = isset($_POST['ntllms_txt_builder_ignore_page']) ? '1' : '0';

        update_post_meta($post_id, '_ntllms_txt_builder_clear_cache', $clear_cache);
        update_post_meta($post_id, '_ntllms_txt_builder_ignore_page', $ignore_page);

        if ($clear_cache === '1') {
            LLMs_TXT_Generator::get_instance()->cache->clear_all_cache();
        }
    }

    /**
     * Register REST hooks for cache clearing after block editor saves.
     */
    public function register_rest_hooks() {
        $post_types = get_post_types(array('public' => true));
        foreach ($post_types as $post_type) {
            add_action("rest_after_insert_{$post_type}", array($this, 'on_rest_after_insert'), 10, 2);
        }
    }

    /**
     * Clear cache after a REST API save if the clear-cache flag is set.
     *
     * @param \WP_Post         $post    Inserted or updated post object.
     * @param \WP_REST_Request $request Request object.
     */
    public function on_rest_after_insert($post, $request) {
        $clear = get_post_meta($post->ID, '_ntllms_txt_builder_clear_cache', true);
        if ($clear) {
            LLMs_TXT_Generator::get_instance()->cache->clear_all_cache();
        }
    }
}
