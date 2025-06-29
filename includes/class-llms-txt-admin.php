<?php
/**
 * Admin functionality for LLMs.txt Generator
 * 
 * @package LLMs_TXT_Generator
 * @since 1.0.0
 */

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
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'admin_init'));
        add_action('wp_ajax_generate_llms_txt', array($this, 'ajax_generate_llms_txt'));
        add_action('wp_ajax_clear_llms_txt_cache', array($this, 'ajax_clear_cache'));
        add_filter('plugin_action_links_' . plugin_basename(LLMS_TXT_PLUGIN_FILE), array($this, 'add_plugin_action_links'));
    }
    
    /**
     * Add plugin action links
     *
     * @param array $links Plugin action links
     * @return array Modified plugin action links
     */
    public function add_plugin_action_links($links) {
        // Add Settings link
        $settings_link = '<a href="' . admin_url('options-general.php?page=llms-txt-settings') . '">' . __('Settings', 'llms-txt-wordpress') . '</a>';
        array_unshift($links, $settings_link);
        
        // Add Donate link
        $donate_link = '<a href="https://buymeacoffee.com/nutttaro" target="_blank" style="color: #d63638; font-weight: 600;">' . __('Donate', 'llms-txt-wordpress') . '</a>';
        $links[] = $donate_link;
        
        return $links;
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_options_page(
            __('LLMs.txt Settings', 'llms-txt-wordpress'),
            __('LLMs.txt', 'llms-txt-wordpress'),
            'manage_options',
            'llms-txt-settings',
            array($this, 'admin_page')
        );
    }
    
    /**
     * Admin initialization
     */
    public function admin_init() {
        register_setting('llms_txt_settings', 'llms_txt_options', array($this, 'sanitize_options'));
        
        add_settings_section(
            'llms_txt_general',
            __('General Settings', 'llms-txt-wordpress'),
            array($this, 'general_section_callback'),
            'llms_txt_settings'
        );
        
        // Post types settings
        $post_types = $this->get_available_post_types();
        foreach ($post_types as $post_type) {
            add_settings_field(
                'post_type_' . $post_type->name,
                sprintf(__('Include %s', 'llms-txt-wordpress'), $post_type->labels->name),
                array($this, 'checkbox_field_callback'),
                'llms_txt_settings',
                'llms_txt_general',
                array(
                    'field' => 'post_types',
                    'value' => $post_type->name,
                    'label' => $post_type->labels->name
                )
            );
        }
        
        // Taxonomies settings
        $taxonomies = $this->get_available_taxonomies();
        foreach ($taxonomies as $taxonomy) {
            add_settings_field(
                'taxonomy_' . $taxonomy->name,
                sprintf(__('Include %s', 'llms-txt-wordpress'), $taxonomy->labels->name),
                array($this, 'checkbox_field_callback'),
                'llms_txt_settings',
                'llms_txt_general',
                array(
                    'field' => 'taxonomies',
                    'value' => $taxonomy->name,
                    'label' => $taxonomy->labels->name
                )
            );
        }
        
        // Additional settings
        add_settings_field(
            'include_pages',
            __('Include Pages', 'llms-txt-wordpress'),
            array($this, 'checkbox_field_callback'),
            'llms_txt_settings',
            'llms_txt_general',
            array(
                'field' => 'include_pages',
                'value' => '1',
                'label' => __('Include static pages', 'llms-txt-wordpress')
            )
        );
        
        add_settings_field(
            'include_archives',
            __('Include Archives', 'llms-txt-wordpress'),
            array($this, 'checkbox_field_callback'),
            'llms_txt_settings',
            'llms_txt_general',
            array(
                'field' => 'include_archives',
                'value' => '1',
                'label' => __('Include archive pages', 'llms-txt-wordpress')
            )
        );
        
        add_settings_field(
            'include_author_pages',
            __('Include Author Pages', 'llms-txt-wordpress'),
            array($this, 'checkbox_field_callback'),
            'llms_txt_settings',
            'llms_txt_general',
            array(
                'field' => 'include_author_pages',
                'value' => '1',
                'label' => __('Include author archive pages', 'llms-txt-wordpress')
            )
        );
        
        add_settings_field(
            'custom_overview_text',
            __('Custom Overview Text', 'llms-txt-wordpress'),
            array($this, 'textarea_field_callback'),
            'llms_txt_settings',
            'llms_txt_general',
            array(
                'field' => 'custom_overview_text',
                'description' => __('Custom text to display in the Project Overview section of LLMs.txt. Leave empty to use default text.', 'llms-txt-wordpress')
            )
        );
    }
    
    /**
     * Get available post types
     */
    private function get_available_post_types() {
        $post_types = get_post_types(array('public' => true), 'objects');
        unset($post_types['attachment']); // Exclude attachments
        return $post_types;
    }
    
    /**
     * Get available taxonomies
     */
    private function get_available_taxonomies() {
        return get_taxonomies(array('public' => true), 'objects');
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
        
        $sanitized['include_pages'] = isset($input['include_pages']) ? '1' : '0';
        $sanitized['include_archives'] = isset($input['include_archives']) ? '1' : '0';
        $sanitized['include_author_pages'] = isset($input['include_author_pages']) ? '1' : '0';
        
        if (isset($input['custom_overview_text'])) {
            $sanitized['custom_overview_text'] = sanitize_textarea_field($input['custom_overview_text']);
        }
        
        // Clear cache when settings are updated
        $llms_txt = LLMs_TXT_Generator::get_instance();
        $llms_txt->cache->clear_cache();
        
        return $sanitized;
    }
    
    /**
     * General section callback
     */
    public function general_section_callback() {
        echo '<p>' . __('Select which content types to include in your LLMs.txt file:', 'llms-txt-wordpress') . '</p>';
    }
    
    /**
     * Checkbox field callback
     */
    public function checkbox_field_callback($args) {
        $options = get_option('llms_txt_options', array());
        $field = $args['field'];
        $value = $args['value'];
        $label = $args['label'];
        
        if ($field === 'post_types' || $field === 'taxonomies') {
            $checked = isset($options[$field]) && in_array($value, $options[$field]);
        } else {
            $checked = isset($options[$field]) && $options[$field] === $value;
        }
        
        echo '<label><input type="checkbox" name="llms_txt_options[' . esc_attr($field) . '][]" value="' . esc_attr($value) . '" ' . checked($checked, true, false) . ' /> ' . esc_html($label) . '</label>';
    }
    
    /**
     * Textarea field callback
     */
    public function textarea_field_callback($args) {
        $options = get_option('llms_txt_options', array());
        $field = $args['field'];
        $description = $args['description'];
        
        echo '<textarea name="llms_txt_options[' . esc_attr($field) . ']" rows="4" cols="50">' . esc_textarea($options[$field]) . '</textarea>';
        echo '<p class="description">' . esc_html($description) . '</p>';
    }
    
    /**
     * Admin page
     */
    public function admin_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('LLMs.txt Settings', 'llms-txt-wordpress'); ?></h1>
            
            <form method="post" action="options.php">
                <?php
                settings_fields('llms_txt_settings');
                do_settings_sections('llms_txt_settings');
                submit_button();
                ?>
            </form>
            
            <hr>
            
            <h2><?php _e('Generate LLMs.txt', 'llms-txt-wordpress'); ?></h2>
            <p><?php _e('Click the button below to manually generate the LLMs.txt file:', 'llms-txt-wordpress'); ?></p>
            
            <button type="button" id="generate-llms-txt" class="button button-primary">
                <?php _e('Generate LLMs.txt', 'llms-txt-wordpress'); ?>
            </button>
            
            <button type="button" id="clear-cache" class="button button-secondary">
                <?php _e('Clear Cache', 'llms-txt-wordpress'); ?>
            </button>
            
            <div id="llms-txt-status" style="margin-top: 10px;"></div>
            
            <hr>
            
            <h2><?php _e('LLMs.txt URL', 'llms-txt-wordpress'); ?></h2>
            <p><?php _e('Your LLMs.txt file is available at:', 'llms-txt-wordpress'); ?></p>
            <code><?php echo esc_url(home_url('/llms.txt')); ?></code>
            
            <p><a href="<?php echo esc_url(home_url('/llms.txt')); ?>" target="_blank" class="button"><?php _e('View LLMs.txt', 'llms-txt-wordpress'); ?></a></p>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            $('#generate-llms-txt').on('click', function() {
                var button = $(this);
                var status = $('#llms-txt-status');
                
                button.prop('disabled', true).text('<?php _e('Generating...', 'llms-txt-wordpress'); ?>');
                status.html('<p><?php _e('Generating LLMs.txt file...', 'llms-txt-wordpress'); ?></p>');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'generate_llms_txt',
                        nonce: '<?php echo wp_create_nonce('llms_txt_generate'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            status.html('<p style="color: green;"><?php _e('LLMs.txt file generated successfully!', 'llms-txt-wordpress'); ?></p>');
                        } else {
                            status.html('<p style="color: red;"><?php _e('Error generating LLMs.txt file.', 'llms-txt-wordpress'); ?></p>');
                        }
                    },
                    error: function() {
                        status.html('<p style="color: red;"><?php _e('Error generating LLMs.txt file.', 'llms-txt-wordpress'); ?></p>');
                    },
                    complete: function() {
                        button.prop('disabled', false).text('<?php _e('Generate LLMs.txt', 'llms-txt-wordpress'); ?>');
                    }
                });
            });
            
            $('#clear-cache').on('click', function() {
                var button = $(this);
                var status = $('#llms-txt-status');
                
                button.prop('disabled', true).text('<?php _e('Clearing...', 'llms-txt-wordpress'); ?>');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'clear_llms_txt_cache',
                        nonce: '<?php echo wp_create_nonce('llms_txt_clear_cache'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            status.html('<p style="color: green;"><?php _e('Cache cleared successfully!', 'llms-txt-wordpress'); ?></p>');
                        } else {
                            status.html('<p style="color: red;"><?php _e('Error clearing cache.', 'llms-txt-wordpress'); ?></p>');
                        }
                    },
                    error: function() {
                        status.html('<p style="color: red;"><?php _e('Error clearing cache.', 'llms-txt-wordpress'); ?></p>');
                    },
                    complete: function() {
                        button.prop('disabled', false).text('<?php _e('Clear Cache', 'llms-txt-wordpress'); ?>');
                    }
                });
            });
        });
        </script>
        <?php
    }
    
    /**
     * AJAX generate LLMs.txt
     */
    public function ajax_generate_llms_txt() {
        check_ajax_referer('llms_txt_generate', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die();
        }
        
        $llms_txt = LLMs_TXT_Generator::get_instance();
        $result = $llms_txt->generator->generate_llms_txt();
        
        if ($result) {
            wp_send_json_success();
        } else {
            wp_send_json_error();
        }
    }
    
    /**
     * AJAX clear cache
     */
    public function ajax_clear_cache() {
        check_ajax_referer('llms_txt_clear_cache', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die();
        }
        
        $llms_txt = LLMs_TXT_Generator::get_instance();
        $result = $llms_txt->cache->clear_cache();
        
        if ($result) {
            wp_send_json_success();
        } else {
            wp_send_json_error();
        }
    }
} 