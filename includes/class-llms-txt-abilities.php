<?php
/**
 * WordPress 7.0 Abilities API integration
 *
 * Registers the plugin's llms.txt generation as a discoverable ability
 * so AI agents can find and consume the site's content index.
 *
 * Guarded with function_exists() checks — fully backward-compatible with WP < 7.0.
 *
 * @package LLMs_TXT_Generator
 * @since 1.2.0
 */

namespace NT\LLMSTXT;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Abilities API class for LLMs.txt Generator
 */
class LLMs_TXT_Abilities {

    /**
     * Constructor
     */
    public function __construct() {
        add_action('wp_abilities_api_categories_init', array($this, 'register_categories'));
        add_action('wp_abilities_api_init', array($this, 'register_abilities'));
    }

    /**
     * Register the content-discovery ability category.
     */
    public function register_categories() {
        if (!function_exists('wp_register_ability_category')) {
            return;
        }

        wp_register_ability_category('content-discovery', array(
            'label'       => __('Content Discovery', 'nt-llms-txt-builder'),
            'description' => __('Abilities for discovering and accessing site content structure.', 'nt-llms-txt-builder'),
        ));
    }

    /**
     * Register the llms.txt ability.
     */
    public function register_abilities() {
        if (!function_exists('wp_register_ability')) {
            return;
        }

        wp_register_ability('nt-llms-txt-builder/get-llms-txt', array(
            'label'       => __('Get LLMs.txt', 'nt-llms-txt-builder'),
            'description' => __('Returns the llms.txt content — a structured markdown index of all published content URLs and titles on this site, following the llms.txt specification.', 'nt-llms-txt-builder'),
            'category'    => 'content-discovery',
            'input_schema' => array(
                'type'       => 'object',
                'properties' => array(
                    'variant' => array(
                        'type'        => 'string',
                        'description' => __('Content variant: "standard" for titles and URLs only, "full" for titles, URLs, and excerpts.', 'nt-llms-txt-builder'),
                        'enum'        => array('standard', 'full'),
                        'default'     => 'standard',
                    ),
                ),
            ),
            'output_schema' => array(
                'type'       => 'object',
                'properties' => array(
                    'content' => array(
                        'type'        => 'string',
                        'description' => __('The llms.txt content in markdown format.', 'nt-llms-txt-builder'),
                    ),
                    'endpoint' => array(
                        'type'        => 'string',
                        'description' => __('The public URL of this llms.txt endpoint.', 'nt-llms-txt-builder'),
                    ),
                ),
            ),
            'execute_callback'    => array($this, 'execute_get_llms_txt'),
            'permission_callback' => '__return_true',
            'show_in_rest'        => true,
            'idempotent'          => true,
            'destructive'         => false,
        ));
    }

    /**
     * Execute callback for the get-llms-txt ability.
     *
     * @param array $input Input parameters.
     * @return array
     */
    public function execute_get_llms_txt($input) {
        $variant   = isset($input['variant']) ? $input['variant'] : 'standard';
        $generator = LLMs_TXT_Generator::get_instance()->generator;

        if ('full' === $variant) {
            $content  = $generator->get_llms_full_txt_content();
            $endpoint = home_url('/llms-full.txt');
        } else {
            $content  = $generator->get_llms_txt_content();
            $endpoint = home_url('/llms.txt');
        }

        return array(
            'content'  => $content,
            'endpoint' => $endpoint,
        );
    }
}
