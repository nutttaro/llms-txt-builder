<?php
/**
 * Meta Box functionality for LLMs.txt Generator
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
        add_action('add_meta_boxes', array($this, 'add_meta_box'));
        add_action('save_post', array($this, 'save_meta_box_data'));
    }
    
    /**
     * Add meta box
     */
    public function add_meta_box() {
        $post_types = get_post_types(array('public' => true));
        
        foreach ($post_types as $post_type) {
            add_meta_box(
                'nt_llms_txt_builder_cache_clear',
                esc_html__('NT LLMs.txt Builder', 'llms-txt-builder'),
                array($this, 'meta_box_callback'),
                $post_type,
                'side',
                'default'
            );
        }
    }
    
    /**
     * Meta box callback
     */
    public function meta_box_callback($post) {
        wp_nonce_field('nt_llms_txt_builder_meta_box', 'nt_llms_txt_builder_meta_box_nonce');
        
        $clear_cache = get_post_meta($post->ID, '_nt_llms_txt_builder_clear_cache', true);
        $ignore_page = get_post_meta($post->ID, '_nt_llms_txt_builder_ignore_page', true);
        
        ?>
        <p>
            <label>
                <input type="checkbox" name="nt_llms_txt_builder_clear_cache" value="1" <?php checked($clear_cache, '1'); ?> />
                <?php esc_html_e('Clear LLMs.txt cache on update', 'llms-txt-builder'); ?>
            </label>
        </p>
        <p>
            <label>
                <input type="checkbox" name="nt_llms_txt_builder_ignore_page" value="1" <?php checked($ignore_page, '1'); ?> />
                <?php esc_html_e('Ignore this page/post in LLMs.txt', 'llms-txt-builder'); ?>
            </label>
        </p>
        <?php
    }
    
    /**
     * Save meta box data
     */
    public function save_meta_box_data($post_id) {
        // Check if nonce is valid
        if (!isset($_POST['nt_llms_txt_builder_meta_box_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nt_llms_txt_builder_meta_box_nonce'])), 'nt_llms_txt_builder_meta_box')) {
            return;
        }
        
        // Check if user has permissions to save data
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Check if not an autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        // Save meta box data
        $clear_cache = isset($_POST['nt_llms_txt_builder_clear_cache']) ? '1' : '0';
        $ignore_page = isset($_POST['nt_llms_txt_builder_ignore_page']) ? '1' : '0';
        
        update_post_meta($post_id, '_nt_llms_txt_builder_clear_cache', $clear_cache);
        update_post_meta($post_id, '_nt_llms_txt_builder_ignore_page', $ignore_page);
        
        // Clear cache if requested
        if ($clear_cache === '1') {
            $llms_txt = LLMs_TXT_Generator::get_instance();
            $llms_txt->cache->clear_cache();
        }
    }
} 