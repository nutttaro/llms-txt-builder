<?php
/**
 * Admin functionality for LLMs.txt Generator
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
 * Admin class for LLMs.txt Generator
 */
class LLMs_TXT_Admin {

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
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'init_settings'));
        add_action('wp_ajax_ntllms_txt_builder_generate_file', array($this, 'ajax_generate_llms_txt'));
        add_action('wp_ajax_ntllms_txt_builder_clear_cache_data', array($this, 'ajax_clear_cache'));
        add_action('wp_ajax_ntllms_txt_builder_preview', array($this, 'ajax_preview'));
        add_filter('plugin_action_links_' . plugin_basename(NT_LLMS_TXT_BUILDER_PLUGIN_FILE), array($this, 'add_plugin_action_links'));
        add_filter('plugin_row_meta', array($this, 'add_plugin_row_meta'), 10, 2);
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }

    /**
     * Add plugin action links
     */
    public function add_plugin_action_links($links) {
        $settings_link = '<a href="' . admin_url('options-general.php?page=ntllms_txt_builder_settings') . '">' . esc_html__('Settings', 'nt-llms-txt-builder') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }

    /**
     * Add plugin row meta
     */
    public function add_plugin_row_meta($links, $file) {
        if (plugin_basename(NT_LLMS_TXT_BUILDER_PLUGIN_FILE) === $file) {
            $links[] = '<a href="https://wordpress.org/plugins/nt-llms-txt-builder/" target="_blank">' . esc_html__('Documentation', 'nt-llms-txt-builder') . '</a>';
            $links[] = '<a href="https://wordpress.org/support/plugin/nt-llms-txt-builder/" target="_blank">' . esc_html__('Support', 'nt-llms-txt-builder') . '</a>';
            $links[] = '<a href="https://wordpress.org/support/plugin/nt-llms-txt-builder/reviews/" target="_blank">' . esc_html__('Reviews', 'nt-llms-txt-builder') . '</a>';
            $links[] = '<a href="https://buymeacoffee.com/nutttaro" target="_blank" style="font-weight:bold;">' . esc_html__('Donate', 'nt-llms-txt-builder') . '</a>';
        }
        return $links;
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_options_page(
            esc_html__('LLMs.txt Builder Settings', 'nt-llms-txt-builder'),
            esc_html__('LLMs.txt Builder', 'nt-llms-txt-builder'),
            'manage_options',
            'ntllms_txt_builder_settings',
            array($this, 'settings_page')
        );
    }

    /**
     * Register setting for sanitization (no Settings API fields — we render manually)
     */
    public function init_settings() {
        register_setting('ntllms_txt_builder_settings', 'ntllms_txt_builder_options', array($this, 'sanitize_options'));
    }

    /**
     * Sanitize options
     */
    public function sanitize_options($input) {
        $sanitized = array();

        if (isset($input['post_types']) && is_array($input['post_types'])) {
            $sanitized['post_types'] = array_map('sanitize_text_field', $input['post_types']);
        }

        if (isset($input['taxonomies']) && is_array($input['taxonomies'])) {
            $sanitized['taxonomies'] = array_map('sanitize_text_field', $input['taxonomies']);
        }

        $sanitized['include_archives'] = isset($input['include_archives']) ? '1' : '0';
        $sanitized['include_author_pages'] = isset($input['include_author_pages']) ? '1' : '0';

        if (isset($input['overview_text'])) {
            $sanitized['overview_text'] = sanitize_textarea_field($input['overview_text']);
        }

        return $sanitized;
    }

    // ------------------------------------------------------------------
    // Settings page — card-based layout
    // ------------------------------------------------------------------

    public function settings_page() {
        $options    = get_option('ntllms_txt_builder_options', array());
        $cache      = LLMs_TXT_Generator::get_instance()->cache;
        $has_cache  = $cache->has_cache();
        $last_gen   = get_option('ntllms_txt_builder_last_generated', 0);
        ?>
        <div class="wrap">

            <!-- Header -->
            <div class="ntllms-header">
                <h1><?php esc_html_e('LLMs.txt Builder', 'nt-llms-txt-builder'); ?></h1>
                <span class="ntllms-version"><?php echo esc_html('v' . NT_LLMS_TXT_BUILDER_PLUGIN_VERSION); ?></span>
            </div>

            <!-- Endpoints + cache status -->
            <?php $this->render_endpoints_card($has_cache, $last_gen); ?>

            <form method="post" action="options.php">
                <?php settings_fields('ntllms_txt_builder_settings'); ?>

                <div class="ntllms-columns">

                    <!-- Left column: settings -->
                    <div>
                        <?php $this->render_content_card($options); ?>
                        <?php $this->render_overview_card($options); ?>

                        <div class="ntllms-save-bar">
                            <?php submit_button(null, 'primary', 'submit', false); ?>
                        </div>
                    </div>

                    <!-- Right column: preview -->
                    <div class="ntllms-preview-panel">
                        <?php $this->render_preview_card(); ?>
                    </div>

                </div>
            </form>

        </div>
        <?php
    }

    /**
     * Endpoints card with cache status bar
     */
    private function render_endpoints_card($has_cache, $last_gen) {
        $standard_url    = home_url('/llms.txt');
        $full_url        = home_url('/llms-full.txt');
        $rules_flushed   = $this->are_rewrite_rules_flushed();
        ?>
        <div class="ntllms-card">
            <h2><span class="dashicons dashicons-admin-links"></span> <?php esc_html_e('Your Endpoints', 'nt-llms-txt-builder'); ?></h2>
            <p class="ntllms-card-desc"><?php esc_html_e('These URLs serve your llms.txt files to AI models and crawlers.', 'nt-llms-txt-builder'); ?></p>

            <?php if (!$rules_flushed) : ?>
                <div class="ntllms-permalink-notice">
                    <span class="dashicons dashicons-warning"></span>
                    <span>
                        <?php
                        printf(
                            /* translators: %s: link to Permalinks settings page */
                            esc_html__('Permalinks need to be refreshed for the endpoints to work. Please visit %s and click "Save Changes", or deactivate and reactivate this plugin.', 'nt-llms-txt-builder'),
                            '<a href="' . esc_url(admin_url('options-permalink.php')) . '">' . esc_html__('Settings > Permalinks', 'nt-llms-txt-builder') . '</a>'
                        );
                        ?>
                    </span>
                </div>
            <?php endif; ?>

            <div class="ntllms-endpoints">
                <div class="ntllms-endpoint-row">
                    <span class="ntllms-endpoint-label"><?php esc_html_e('Standard', 'nt-llms-txt-builder'); ?></span>
                    <span class="ntllms-endpoint-url"><?php echo esc_url($standard_url); ?></span>
                    <span class="ntllms-endpoint-actions">
                        <button type="button" class="button ntllms-copy-btn" data-url="<?php echo esc_attr($standard_url); ?>" title="<?php esc_attr_e('Copy URL', 'nt-llms-txt-builder'); ?>"><span class="dashicons dashicons-clipboard"></span></button>
                        <a href="<?php echo esc_url($standard_url); ?>" target="_blank" class="button" title="<?php esc_attr_e('Open in new tab', 'nt-llms-txt-builder'); ?>"><span class="dashicons dashicons-external"></span></a>
                    </span>
                </div>
                <div class="ntllms-endpoint-row">
                    <span class="ntllms-endpoint-label"><?php esc_html_e('Full', 'nt-llms-txt-builder'); ?></span>
                    <span class="ntllms-endpoint-url"><?php echo esc_url($full_url); ?></span>
                    <span class="ntllms-endpoint-actions">
                        <button type="button" class="button ntllms-copy-btn" data-url="<?php echo esc_attr($full_url); ?>" title="<?php esc_attr_e('Copy URL', 'nt-llms-txt-builder'); ?>"><span class="dashicons dashicons-clipboard"></span></button>
                        <a href="<?php echo esc_url($full_url); ?>" target="_blank" class="button" title="<?php esc_attr_e('Open in new tab', 'nt-llms-txt-builder'); ?>"><span class="dashicons dashicons-external"></span></a>
                    </span>
                </div>
            </div>

            <div class="ntllms-cache-bar">
                <div class="ntllms-cache-info" id="ntllms-cache-status">
                    <span class="ntllms-cache-dot <?php echo $has_cache ? 'active' : 'empty'; ?>"></span>
                    <?php if ($has_cache && $last_gen) : ?>
                        <?php
                        printf(
                            /* translators: %s: human-readable time difference */
                            esc_html__('Cache active — generated %s ago', 'nt-llms-txt-builder'),
                            esc_html(human_time_diff($last_gen))
                        );
                        ?>
                    <?php elseif ($has_cache) : ?>
                        <?php esc_html_e('Cache active', 'nt-llms-txt-builder'); ?>
                    <?php else : ?>
                        <?php esc_html_e('No cache — will generate on next request', 'nt-llms-txt-builder'); ?>
                    <?php endif; ?>
                </div>
                <div class="ntllms-cache-actions">
                    <button type="button" id="generate-llms-txt" class="button button-primary">
                        <span class="dashicons dashicons-update"></span> <?php esc_html_e('Regenerate', 'nt-llms-txt-builder'); ?>
                    </button>
                    <button type="button" id="clear-llms-txt-cache" class="button">
                        <span class="dashicons dashicons-trash"></span> <?php esc_html_e('Clear Cache', 'nt-llms-txt-builder'); ?>
                    </button>
                </div>
            </div>
            <div id="llms-txt-result" style="margin-top: 12px;"></div>
        </div>
        <?php
    }

    /**
     * Content selection card with checkbox grids
     */
    private function render_content_card($options) {
        $post_types = get_post_types(array('public' => true), 'objects');
        $taxonomies = get_taxonomies(array('public' => true), 'objects');
        ?>
        <div class="ntllms-card">
            <h2><span class="dashicons dashicons-category"></span> <?php esc_html_e('Content Selection', 'nt-llms-txt-builder'); ?></h2>
            <p class="ntllms-card-desc"><?php esc_html_e('Choose which content types and taxonomies appear in your llms.txt output.', 'nt-llms-txt-builder'); ?></p>

            <!-- Post Types -->
            <div class="ntllms-checkbox-group" data-group="post_types">
                <div class="ntllms-checkbox-group-header">
                    <h3><?php esc_html_e('Post Types', 'nt-llms-txt-builder'); ?></h3>
                    <span class="ntllms-toggle-links">
                        <a href="#" class="ntllms-select-all"><?php esc_html_e('All', 'nt-llms-txt-builder'); ?></a>
                        <span class="sep">|</span>
                        <a href="#" class="ntllms-select-none"><?php esc_html_e('None', 'nt-llms-txt-builder'); ?></a>
                    </span>
                </div>
                <div class="ntllms-checkbox-list">
                    <?php foreach ($post_types as $pt) :
                        $checked = isset($options['post_types']) && in_array($pt->name, $options['post_types'], true);
                        $count   = wp_count_posts($pt->name);
                        $total   = isset($count->publish) ? (int) $count->publish : 0;
                        ?>
                        <div class="ntllms-checkbox-item">
                            <label>
                                <input type="checkbox"
                                       name="ntllms_txt_builder_options[post_types][]"
                                       value="<?php echo esc_attr($pt->name); ?>"
                                       <?php checked($checked); ?> />
                                <?php echo esc_html($pt->labels->name); ?>
                                <span class="ntllms-count"><?php echo esc_html($total); ?></span>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Taxonomies -->
            <div class="ntllms-checkbox-group" data-group="taxonomies">
                <div class="ntllms-checkbox-group-header">
                    <h3><?php esc_html_e('Taxonomies', 'nt-llms-txt-builder'); ?></h3>
                    <span class="ntllms-toggle-links">
                        <a href="#" class="ntllms-select-all"><?php esc_html_e('All', 'nt-llms-txt-builder'); ?></a>
                        <span class="sep">|</span>
                        <a href="#" class="ntllms-select-none"><?php esc_html_e('None', 'nt-llms-txt-builder'); ?></a>
                    </span>
                </div>
                <div class="ntllms-checkbox-list">
                    <?php foreach ($taxonomies as $tax) :
                        $checked = isset($options['taxonomies']) && in_array($tax->name, $options['taxonomies'], true);
                        $total   = (int) wp_count_terms(array('taxonomy' => $tax->name, 'hide_empty' => true));
                        ?>
                        <div class="ntllms-checkbox-item">
                            <label>
                                <input type="checkbox"
                                       name="ntllms_txt_builder_options[taxonomies][]"
                                       value="<?php echo esc_attr($tax->name); ?>"
                                       <?php checked($checked); ?> />
                                <?php echo esc_html($tax->labels->name); ?>
                                <span class="ntllms-count"><?php echo esc_html($total); ?></span>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Toggle options -->
            <div class="ntllms-toggle-options">
                <div class="ntllms-toggle-option">
                    <label>
                        <input type="checkbox"
                               name="ntllms_txt_builder_options[include_archives]"
                               value="1"
                               <?php checked(isset($options['include_archives']) ? $options['include_archives'] : '1', '1'); ?> />
                        <?php esc_html_e('Include Archives', 'nt-llms-txt-builder'); ?>
                    </label>
                </div>
                <div class="ntllms-toggle-option">
                    <label>
                        <input type="checkbox"
                               name="ntllms_txt_builder_options[include_author_pages]"
                               value="1"
                               <?php checked(isset($options['include_author_pages']) ? $options['include_author_pages'] : '', '1'); ?> />
                        <?php esc_html_e('Include Author Pages', 'nt-llms-txt-builder'); ?>
                    </label>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Overview text card
     */
    private function render_overview_card($options) {
        $value = isset($options['overview_text']) ? $options['overview_text'] : '';
        ?>
        <div class="ntllms-card">
            <h2><span class="dashicons dashicons-editor-quote"></span> <?php esc_html_e('Overview Text', 'nt-llms-txt-builder'); ?></h2>
            <p class="ntllms-card-desc"><?php esc_html_e('Custom description shown at the top of your llms.txt file. Leave empty to use your site tagline.', 'nt-llms-txt-builder'); ?></p>

            <textarea
                name="ntllms_txt_builder_options[overview_text]"
                class="ntllms-overview-textarea"
                placeholder="<?php echo esc_attr(get_bloginfo('description')); ?>"
            ><?php echo esc_textarea($value); ?></textarea>

            <div class="ntllms-overview-hint">
                <span class="dashicons dashicons-info-outline"></span>
                <span>
                    <?php
                    printf(
                        /* translators: %s: example blockquote syntax */
                        esc_html__('This appears as a blockquote in the output: %s', 'nt-llms-txt-builder'),
                        '<code>&gt; Your text here</code>'
                    );
                    ?>
                </span>
            </div>
        </div>
        <?php
    }

    /**
     * Live preview card
     */
    private function render_preview_card() {
        ?>
        <div class="ntllms-card">
            <div class="ntllms-preview-header">
                <h2><span class="dashicons dashicons-visibility"></span> <?php esc_html_e('Live Preview', 'nt-llms-txt-builder'); ?></h2>
                <span class="ntllms-preview-variant">
                    <a href="#" class="active" data-variant="standard"><?php esc_html_e('Standard', 'nt-llms-txt-builder'); ?></a>
                    <a href="#" data-variant="full"><?php esc_html_e('Full', 'nt-llms-txt-builder'); ?></a>
                </span>
            </div>
            <div id="ntllms-preview" class="ntllms-preview-content">
                <div class="ntllms-preview-empty">
                    <span class="dashicons dashicons-welcome-view-site"></span>
                    <?php esc_html_e('Loading preview...', 'nt-llms-txt-builder'); ?>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Check whether our rewrite rules are present in the current rules array.
     */
    private function are_rewrite_rules_flushed() {
        $rules = get_option('rewrite_rules', array());
        if (!is_array($rules)) {
            return false;
        }
        return isset($rules['^llms\.txt$']);
    }

    // ------------------------------------------------------------------
    // AJAX handlers
    // ------------------------------------------------------------------

    public function ajax_generate_llms_txt() {
        check_ajax_referer('ntllms_txt_builder_generate_file', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to perform this action.', 'nt-llms-txt-builder'));
        }

        try {
            $llms_txt = LLMs_TXT_Generator::get_instance();
            $llms_txt->generator->generate_llms_txt();
            update_option('ntllms_txt_builder_last_generated', time());

            wp_send_json_success(array(
                'message'    => esc_html__('LLMs.txt regenerated successfully.', 'nt-llms-txt-builder'),
                'cache_html' => $this->get_cache_status_html(),
            ));
        } catch (\Exception $e) {
            wp_send_json_error(esc_html__('Error generating LLMs.txt: ', 'nt-llms-txt-builder') . $e->getMessage());
        }
    }

    public function ajax_clear_cache() {
        check_ajax_referer('ntllms_txt_builder_clear_cache_data', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to perform this action.', 'nt-llms-txt-builder'));
        }

        try {
            $llms_txt = LLMs_TXT_Generator::get_instance();
            $llms_txt->cache->clear_all_cache();
            delete_option('ntllms_txt_builder_last_generated');

            wp_send_json_success(array(
                'message'    => esc_html__('Cache cleared.', 'nt-llms-txt-builder'),
                'cache_html' => $this->get_cache_status_html(),
            ));
        } catch (\Exception $e) {
            wp_send_json_error(esc_html__('Error clearing cache: ', 'nt-llms-txt-builder') . $e->getMessage());
        }
    }

    /**
     * AJAX: return llms.txt preview content
     */
    public function ajax_preview() {
        check_ajax_referer('ntllms_txt_builder_preview', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        $variant   = isset($_POST['variant']) ? sanitize_text_field(wp_unslash($_POST['variant'])) : 'standard';
        $generator = LLMs_TXT_Generator::get_instance()->generator;

        if ('full' === $variant) {
            $content = $generator->get_llms_full_txt_content();
        } else {
            $content = $generator->get_llms_txt_content();
        }

        wp_send_json_success(array('content' => $content));
    }

    /**
     * Build cache status HTML fragment for AJAX updates
     */
    private function get_cache_status_html() {
        $has_cache = LLMs_TXT_Generator::get_instance()->cache->has_cache();
        $last_gen  = get_option('ntllms_txt_builder_last_generated', 0);

        $dot_class = $has_cache ? 'active' : 'empty';
        $html      = '<span class="ntllms-cache-dot ' . $dot_class . '"></span> ';

        if ($has_cache && $last_gen) {
            $html .= sprintf(
                /* translators: %s: human-readable time difference, e.g. "2 hours" */
                esc_html__('Cache active — generated %s ago', 'nt-llms-txt-builder'),
                esc_html(human_time_diff($last_gen))
            );
        } elseif ($has_cache) {
            $html .= esc_html__('Cache active', 'nt-llms-txt-builder');
        } else {
            $html .= esc_html__('No cache — will generate on next request', 'nt-llms-txt-builder');
        }

        return $html;
    }

    // ------------------------------------------------------------------
    // Asset enqueue
    // ------------------------------------------------------------------

    public function enqueue_admin_scripts($hook) {
        if ($hook !== 'settings_page_ntllms_txt_builder_settings') {
            return;
        }

        $css_file = NT_LLMS_TXT_BUILDER_PLUGIN_PATH . 'assets/css/admin.css';
        $js_file  = NT_LLMS_TXT_BUILDER_PLUGIN_PATH . 'assets/js/admin.js';

        wp_enqueue_style(
            'nt-llms-txt-builder-admin',
            plugins_url('../assets/css/admin.css', __FILE__),
            array(),
            file_exists($css_file) ? filemtime($css_file) : NT_LLMS_TXT_BUILDER_PLUGIN_VERSION
        );

        wp_enqueue_script(
            'nt-llms-txt-builder-admin',
            plugins_url('../assets/js/admin.js', __FILE__),
            array('jquery'),
            file_exists($js_file) ? filemtime($js_file) : NT_LLMS_TXT_BUILDER_PLUGIN_VERSION,
            true
        );

        wp_localize_script(
            'nt-llms-txt-builder-admin',
            'nt_llms_txt_builder',
            array(
                'generate_nonce'    => wp_create_nonce('ntllms_txt_builder_generate_file'),
                'clear_cache_nonce' => wp_create_nonce('ntllms_txt_builder_clear_cache_data'),
                'preview_nonce'     => wp_create_nonce('ntllms_txt_builder_preview'),
                'i18n'              => array(
                    'generating'  => esc_html__('Regenerating...', 'nt-llms-txt-builder'),
                    'regenerate'  => esc_html__('Regenerate', 'nt-llms-txt-builder'),
                    'clearing'    => esc_html__('Clearing...', 'nt-llms-txt-builder'),
                    'clear_cache' => esc_html__('Clear Cache', 'nt-llms-txt-builder'),
                    'error'       => esc_html__('An error occurred. Please try again.', 'nt-llms-txt-builder'),
                    'copied'      => esc_html__('Copied!', 'nt-llms-txt-builder'),
                    'loading'     => esc_html__('Loading preview...', 'nt-llms-txt-builder'),
                    'empty'       => esc_html__('No content to preview. Save your settings first.', 'nt-llms-txt-builder'),
                ),
            )
        );
    }
}
