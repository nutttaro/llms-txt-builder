<?php
/**
 * Main LLMs.txt Generator Class
 * 
 * @package LLMs_TXT_Generator
 * @since 1.0.0
 */

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
        add_action('init', array($this, 'init'));
        add_action('init', array($this, 'handle_llms_txt_request'));
        register_activation_hook(LLMS_TXT_PLUGIN_FILE, array($this, 'activate'));
        register_deactivation_hook(LLMS_TXT_PLUGIN_FILE, array($this, 'deactivate'));
    }
    
    /**
     * Load plugin dependencies
     */
    private function load_dependencies() {
        // Load admin functionality
        require_once LLMS_TXT_PLUGIN_PATH . 'includes/class-llms-txt-admin.php';
        $this->admin = new LLMs_TXT_Admin();
        
        // Load generator functionality
        require_once LLMS_TXT_PLUGIN_PATH . 'includes/class-llms-txt-generator-content.php';
        $this->generator = new LLMs_TXT_Generator_Content();
        
        // Load cache functionality
        require_once LLMS_TXT_PLUGIN_PATH . 'includes/class-llms-txt-cache.php';
        $this->cache = new LLMs_TXT_Cache();
        
        // Load meta box functionality
        require_once LLMS_TXT_PLUGIN_PATH . 'includes/class-llms-txt-meta-box.php';
        $this->meta_box = new LLMs_TXT_Meta_Box();
    }
    
    /**
     * Initialize plugin
     */
    public function init() {
        load_plugin_textdomain('llms-txt-generator', false, dirname(plugin_basename(LLMS_TXT_PLUGIN_FILE)) . '/languages');
    }
    
    /**
     * Handle LLMs.txt request
     */
    public function handle_llms_txt_request() {
        if (isset($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI'] === '/llms.txt') {
            $content = $this->generator->get_llms_txt_content();
            
            header('Content-Type: text/plain; charset=utf-8');
            header('Cache-Control: public, max-age=' . LLMS_TXT_CACHE_DURATION);
            echo wp_kses_post($content);
            exit;
        }
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Set default options
        $default_options = array(
            'post_types' => array('post'),
            'taxonomies' => array('category', 'post_tag'),
            'include_pages' => '1',
            'include_archives' => '1',
            'include_author_pages' => '1'
        );
        
        add_option('llms_txt_options', $default_options);
        
        // Generate initial LLMs.txt
        $this->generator->generate_llms_txt();
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        $this->cache->clear_cache();
        flush_rewrite_rules();
    }
} 