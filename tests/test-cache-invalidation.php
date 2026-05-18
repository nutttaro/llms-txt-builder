<?php
/**
 * Tests for automatic cache invalidation
 */

class Test_Cache_Invalidation extends WP_UnitTestCase {

    private $instance;

    public function set_up() {
        parent::set_up();

        $this->instance = \NT\LLMSTXT\LLMs_TXT_Generator::get_instance();

        update_option('ntllms_txt_builder_options', array(
            'post_types'           => array('post', 'page'),
            'taxonomies'           => array('category'),
            'include_archives'     => '0',
            'include_author_pages' => '0',
        ));
    }

    public function tear_down() {
        delete_transient(NT_LLMS_TXT_BUILDER_CACHE_KEY);
        delete_transient(NT_LLMS_TXT_BUILDER_FULL_CACHE_KEY);
        parent::tear_down();
    }

    public function test_cache_cleared_when_tracked_post_published() {
        set_transient(NT_LLMS_TXT_BUILDER_CACHE_KEY, 'stale', 3600);
        set_transient(NT_LLMS_TXT_BUILDER_FULL_CACHE_KEY, 'stale-full', 3600);

        $post_id = self::factory()->post->create(array('post_status' => 'draft'));
        wp_publish_post($post_id);

        $this->assertFalse(get_transient(NT_LLMS_TXT_BUILDER_CACHE_KEY));
        $this->assertFalse(get_transient(NT_LLMS_TXT_BUILDER_FULL_CACHE_KEY));
    }

    public function test_cache_not_cleared_for_untracked_post_type() {
        update_option('ntllms_txt_builder_options', array_merge(
            get_option('ntllms_txt_builder_options'),
            array('post_types' => array('page'))
        ));

        set_transient(NT_LLMS_TXT_BUILDER_CACHE_KEY, 'should-stay', 3600);

        $post_id = self::factory()->post->create(array('post_status' => 'draft'));
        wp_publish_post($post_id);

        $this->assertSame('should-stay', get_transient(NT_LLMS_TXT_BUILDER_CACHE_KEY));
    }

    public function test_cache_cleared_when_post_trashed() {
        $post_id = self::factory()->post->create(array('post_status' => 'publish'));

        set_transient(NT_LLMS_TXT_BUILDER_CACHE_KEY, 'stale', 3600);
        wp_trash_post($post_id);

        $this->assertFalse(get_transient(NT_LLMS_TXT_BUILDER_CACHE_KEY));
    }

    public function test_cache_cleared_when_tracked_term_created() {
        set_transient(NT_LLMS_TXT_BUILDER_CACHE_KEY, 'stale', 3600);

        wp_insert_term('New Category', 'category');

        $this->assertFalse(get_transient(NT_LLMS_TXT_BUILDER_CACHE_KEY));
    }

    public function test_clear_all_cache_removes_both_transients() {
        set_transient(NT_LLMS_TXT_BUILDER_CACHE_KEY, 'data', 3600);
        set_transient(NT_LLMS_TXT_BUILDER_FULL_CACHE_KEY, 'data-full', 3600);

        $this->instance->cache->clear_all_cache();

        $this->assertFalse(get_transient(NT_LLMS_TXT_BUILDER_CACHE_KEY));
        $this->assertFalse(get_transient(NT_LLMS_TXT_BUILDER_FULL_CACHE_KEY));
    }
}
