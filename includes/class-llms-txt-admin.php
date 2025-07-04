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
        add_filter('plugin_action_links_' . plugin_basename(NT_LLMS_TXT_BUILDER_PLUGIN_FILE), array($this, 'add_plugin_action_links'));
        add_filter('plugin_row_meta', array($this, 'add_plugin_row_meta'), 10, 2);
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }
    
    /**
     * Add plugin action links
     *
     * @param array $links Plugin action links
     * @return array Modified plugin action links
     */
    public function add_plugin_action_links($links) {
        // Add Settings link
        $settings_link = '<a href="' . admin_url('options-general.php?page=ntllms_txt_builder_settings') . '">' . esc_html__('Settings', 'nt-llms-txt-builder') . '</a>';
        array_unshift($links, $settings_link);
        
        return $links;
    }
    
    /**
     * Add plugin row meta
     *
     * @param array $links Plugin row meta links
     * @param string $file Plugin file
     * @return array Modified plugin row meta links
     */
    public function add_plugin_row_meta($links, $file) {
        if (plugin_basename(NT_LLMS_TXT_BUILDER_PLUGIN_FILE) === $file) {
            $links[] = '<a href="https://wordpress.org/plugins/nt-llms-txt-builder/" target="_blank">' . esc_html__('Documentation', 'nt-llms-txt-builder') . '</a>';
            $links[] = '<a href="https://wordpress.org/support/plugin/nt-llms-txt-builder/" target="_blank">' . esc_html__('Support', 'nt-llms-txt-builder') . '</a>';
            $links[] = '<a href="https://wordpress.org/support/plugin/nt-llms-txt-builder/reviews/" target="_blank">' . esc_html__('Reviews', 'nt-llms-txt-builder') . '</a>';
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
     * Initialize settings
     */
    public function init_settings() {
        register_setting('ntllms_txt_builder_settings', 'ntllms_txt_builder_options', array($this, 'sanitize_options'));
        
        add_settings_section(
            'ntllms_txt_builder_general',
            esc_html__('General Settings', 'nt-llms-txt-builder'),
            array($this, 'general_section_callback'),
            'ntllms_txt_builder_settings'
        );
        
        add_settings_field(
            'post_types',
            esc_html__('Post Types', 'nt-llms-txt-builder'),
            array($this, 'post_types_callback'),
            'ntllms_txt_builder_settings',
            'ntllms_txt_builder_general'
        );
        
        add_settings_field(
            'taxonomies',
            esc_html__('Taxonomies', 'nt-llms-txt-builder'),
            array($this, 'taxonomies_callback'),
            'ntllms_txt_builder_settings',
            'ntllms_txt_builder_general'
        );
        

        
        add_settings_field(
            'include_archives',
            esc_html__('Include Archives', 'nt-llms-txt-builder'),
            array($this, 'include_archives_callback'),
            'ntllms_txt_builder_settings',
            'ntllms_txt_builder_general'
        );
        
        add_settings_field(
            'include_author_pages',
            esc_html__('Include Author Pages', 'nt-llms-txt-builder'),
            array($this, 'include_author_pages_callback'),
            'ntllms_txt_builder_settings',
            'ntllms_txt_builder_general'
        );
        
        add_settings_field(
            'overview_text',
            esc_html__('Overview Text', 'nt-llms-txt-builder'),
            array($this, 'overview_text_callback'),
            'ntllms_txt_builder_settings',
            'ntllms_txt_builder_general'
        );
    }
    
    /**
     * General section callback
     */
    public function general_section_callback() {
        echo '<p>' . esc_html__('Configure which content types to include in your LLMs.txt file.', 'nt-llms-txt-builder') . '</p>';
    }
    
    /**
     * Post types callback
     */
    public function post_types_callback() {
        $options = get_option('ntllms_txt_builder_options', array());
        $post_types = get_post_types(array('public' => true), 'objects');
        
        foreach ($post_types as $post_type) {
            $field = 'post_types';
            $value = $post_type->name;
            $label = $post_type->labels->name;
            $checked = isset($options[$field]) && in_array($value, $options[$field]);
            
            echo '<label><input type="checkbox" name="ntllms_txt_builder_options[' . esc_attr($field) . '][]" value="' . esc_attr($value) . '" ' . checked($checked, true, false) . ' /> ' . esc_html($label) . '</label><br>';
        }
    }
    
    /**
     * Taxonomies callback
     */
    public function taxonomies_callback() {
        $options = get_option('ntllms_txt_builder_options', array());
        $taxonomies = get_taxonomies(array('public' => true), 'objects');
        
        foreach ($taxonomies as $taxonomy) {
            $field = 'taxonomies';
            $value = $taxonomy->name;
            $label = $taxonomy->labels->name;
            $checked = isset($options[$field]) && in_array($value, $options[$field]);
            
            echo '<label><input type="checkbox" name="ntllms_txt_builder_options[' . esc_attr($field) . '][]" value="' . esc_attr($value) . '" ' . checked($checked, true, false) . ' /> ' . esc_html($label) . '</label><br>';
        }
    }
    

    
    /**
     * Include archives callback
     */
    public function include_archives_callback() {
        $options = get_option('ntllms_txt_builder_options', array());
        $field = 'include_archives';
        $checked = isset($options[$field]) ? $options[$field] : '1';
        
        echo '<input type="checkbox" name="ntllms_txt_builder_options[' . esc_attr($field) . ']" value="1" ' . checked($checked, '1', false) . ' />';
        echo '<p class="description">' . esc_html__('Check to include archives in the LLMs.txt file.', 'nt-llms-txt-builder') . '</p>';
    }
    
    /**
     * Include author pages callback
     */
    public function include_author_pages_callback() {
        $options = get_option('ntllms_txt_builder_options', array());
        $field = 'include_author_pages';
        $checked = isset($options[$field]) ? $options[$field] : '';
        
        echo '<input type="checkbox" name="ntllms_txt_builder_options[' . esc_attr($field) . ']" value="1" ' . checked($checked, '1', false) . ' />';
        echo '<p class="description">' . esc_html__('Check to include author pages in the LLMs.txt file.', 'nt-llms-txt-builder') . '</p>';
    }
    
    /**
     * Overview text callback
     */
    public function overview_text_callback() {
        $options = get_option('ntllms_txt_builder_options', array());
        $field = 'overview_text';
        $value = isset($options[$field]) ? $options[$field] : '';
        
        echo '<textarea name="ntllms_txt_builder_options[' . esc_attr($field) . ']" rows="4" cols="50">' . esc_textarea($value) . '</textarea>';
        echo '<p class="description">' . esc_html__('Custom text to display at the top of the LLMs.txt file.', 'nt-llms-txt-builder') . '</p>';
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
    
    /**
     * Settings page
     */
    public function settings_page() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('LLMs.txt Builder Settings', 'nt-llms-txt-builder'); ?></h1>
            
            <form method="post" action="options.php">
                <?php
                settings_fields('ntllms_txt_builder_settings');
                do_settings_sections('ntllms_txt_builder_settings');
                submit_button();
                ?>
            </form>
            
            <hr>
            
            <h2><?php esc_html_e('Manual Actions', 'nt-llms-txt-builder'); ?></h2>
            <p><?php esc_html_e('Use these buttons to manually generate or clear the LLMs.txt cache.', 'nt-llms-txt-builder'); ?></p>
            
            <button type="button" id="generate-llms-txt" class="button button-primary">
                <?php esc_html_e('Generate LLMs.txt', 'nt-llms-txt-builder'); ?>
            </button>
            
            <button type="button" id="clear-llms-txt-cache" class="button button-secondary">
                <?php esc_html_e('Clear Cache', 'nt-llms-txt-builder'); ?>
            </button>
            
            <div id="llms-txt-result"></div>
            
            <hr>
            <h2><?php esc_html_e('LLMs.txt URL', 'nt-llms-txt-builder'); ?></h2>
            <p><?php esc_html_e('Your LLMs.txt file is available at:', 'nt-llms-txt-builder'); ?></p>
            <code><?php echo esc_url(home_url('/llms.txt')); ?></code>
            <p><a href="<?php echo esc_url(home_url('/llms.txt')); ?>" target="_blank" class="button"><?php esc_html_e('View LLMs.txt', 'nt-llms-txt-builder'); ?></a></p>
        </div>
        <?php
    }
    
    /**
     * AJAX generate LLMs.txt
     */
    public function ajax_generate_llms_txt() {
        check_ajax_referer('ntllms_txt_builder_generate_file', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to perform this action.', 'nt-llms-txt-builder'));
        }
        
        try {
            $llms_txt = LLMs_TXT_Generator::get_instance();
            $content = $llms_txt->generator->generate_llms_txt();
            
            wp_send_json_success(esc_html__('LLMs.txt generated successfully!', 'nt-llms-txt-builder'));
        } catch (\Exception $e) {
            wp_send_json_error(esc_html__('Error generating LLMs.txt: ', 'nt-llms-txt-builder') . $e->getMessage());
        }
    }
    
    /**
     * AJAX clear cache
     */
    public function ajax_clear_cache() {
        check_ajax_referer('ntllms_txt_builder_clear_cache_data', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to perform this action.', 'nt-llms-txt-builder'));
        }
        
        try {
            $llms_txt = LLMs_TXT_Generator::get_instance();
            $llms_txt->cache->clear_cache();
            
            wp_send_json_success(esc_html__('Cache cleared successfully!', 'nt-llms-txt-builder'));
        } catch (\Exception $e) {
            wp_send_json_error(esc_html__('Error clearing cache: ', 'nt-llms-txt-builder') . $e->getMessage());
        }
    }
    
    /**
     * Enqueue admin scripts for settings page
     */
    public function enqueue_admin_scripts($hook) {
        if ($hook !== 'settings_page_ntllms_txt_builder_settings') {
            return;
        }
        wp_enqueue_script(
            'nt-llms-txt-builder-admin',
            plugins_url('../assets/js/admin.js', __FILE__),
            array('jquery'),
            '1.0.0',
            true
        );
        wp_localize_script(
            'nt-llms-txt-builder-admin',
            'nt_llms_txt_builder',
            array(
                'generate_nonce' => wp_create_nonce('ntllms_txt_builder_generate_file'),
                'clear_cache_nonce' => wp_create_nonce('ntllms_txt_builder_clear_cache_data'),
                'generate_text' => esc_html__('Generating...', 'nt-llms-txt-builder'),
                'generate_button_text' => esc_html__('Generate LLMs.txt', 'nt-llms-txt-builder'),
                'clearing_text' => esc_html__('Clearing...', 'nt-llms-txt-builder'),
                'clear_cache_button_text' => esc_html__('Clear Cache', 'nt-llms-txt-builder'),
                'error_text' => esc_html__('An error occurred.', 'nt-llms-txt-builder'),
            )
        );
    }
} 