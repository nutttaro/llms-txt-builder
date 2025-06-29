<?php
/**
 * Meta Box functionality for LLMs.txt Generator
 * 
 * @package LLMs_TXT_Generator
 * @since 1.0.0
 */

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
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('save_post', array($this, 'save_post_meta'));
    }
    
    /**
     * Add meta boxes
     */
    public function add_meta_boxes() {
        $post_types = get_post_types(array('public' => true));
        unset($post_types['attachment']);
        
        foreach ($post_types as $post_type) {
            add_meta_box(
                'llms_txt_cache_clear',
                __('LLMs.txt Cache', 'llms-txt-wordpress'),
                array($this, 'meta_box_callback'),
                $post_type,
                'side',
                'low'
            );
        }
    }
    
    /**
     * Meta box callback
     */
    public function meta_box_callback($post) {
        wp_nonce_field('llms_txt_meta_box', 'llms_txt_meta_box_nonce');
        
        $clear_cache = get_post_meta($post->ID, '_llms_txt_clear_cache', true);
        $ignore_page = get_post_meta($post->ID, '_llms_txt_ignore_page', true);
        ?>
        <div style="margin-bottom: 15px;">
            <label>
                <input type="checkbox" name="llms_txt_clear_cache" value="1" <?php checked($clear_cache, '1'); ?> />
                <?php _e('Clear LLMs.txt cache when this post is updated', 'llms-txt-wordpress'); ?>
            </label>
            <p class="description"><?php _e('Check this box to automatically clear the LLMs.txt cache when this post is saved.', 'llms-txt-wordpress'); ?></p>
        </div>
        
        <div>
            <label>
                <input type="checkbox" name="llms_txt_ignore_page" value="1" <?php checked($ignore_page, '1'); ?> />
                <?php _e('Exclude this page from LLMs.txt', 'llms-txt-wordpress'); ?>
            </label>
            <p class="description"><?php _e('Check this box to exclude this page from appearing in the generated LLMs.txt file.', 'llms-txt-wordpress'); ?></p>
        </div>
        <?php
    }
    
    /**
     * Save post meta
     */
    public function save_post_meta($post_id) {
        // Check nonce
        if (!isset($_POST['llms_txt_meta_box_nonce']) || !wp_verify_nonce($_POST['llms_txt_meta_box_nonce'], 'llms_txt_meta_box')) {
            return;
        }
        
        // Check permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Check autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        // Save the checkbox values
        $clear_cache = isset($_POST['llms_txt_clear_cache']) ? '1' : '0';
        $ignore_page = isset($_POST['llms_txt_ignore_page']) ? '1' : '0';
        update_post_meta($post_id, '_llms_txt_clear_cache', $clear_cache);
        update_post_meta($post_id, '_llms_txt_ignore_page', $ignore_page);
        
        // Clear cache if checkbox is checked
        if ($clear_cache === '1') {
            $llms_txt = LLMs_TXT_Generator::get_instance();
            $llms_txt->cache->clear_cache();
        }
    }
} 