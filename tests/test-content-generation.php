<?php
/**
 * Tests for LLMs_TXT_Generator_Content
 */

class Test_Content_Generation extends WP_UnitTestCase {

    private $generator;

    public function set_up() {
        parent::set_up();

        $instance = \NT\LLMSTXT\LLMs_TXT_Generator::get_instance();
        $this->generator = $instance->generator;

        update_option('ntllms_txt_builder_options', array(
            'post_types'           => array('post', 'page'),
            'taxonomies'           => array('category', 'post_tag'),
            'include_archives'     => '0',
            'include_author_pages' => '0',
            'overview_text'        => '',
        ));
    }

    public function tear_down() {
        delete_transient(NT_LLMS_TXT_BUILDER_CACHE_KEY);
        delete_transient(NT_LLMS_TXT_BUILDER_FULL_CACHE_KEY);
        parent::tear_down();
    }

    public function test_output_starts_with_h1_site_name() {
        $content = $this->generator->get_llms_txt_content();
        $site_name = get_bloginfo('name');
        $this->assertStringStartsWith("# {$site_name}\n", $content);
    }

    public function test_overview_text_rendered_as_blockquote() {
        update_option('ntllms_txt_builder_options', array_merge(
            get_option('ntllms_txt_builder_options'),
            array('overview_text' => 'My custom overview')
        ));
        delete_transient(NT_LLMS_TXT_BUILDER_CACHE_KEY);

        $content = $this->generator->get_llms_txt_content();
        $this->assertStringContainsString('> My custom overview', $content);
    }

    public function test_pages_listed_with_titles_and_links() {
        $page_id = self::factory()->post->create(array(
            'post_type'   => 'page',
            'post_title'  => 'About Us',
            'post_status' => 'publish',
        ));
        delete_transient(NT_LLMS_TXT_BUILDER_CACHE_KEY);

        $content = $this->generator->get_llms_txt_content();
        $this->assertStringContainsString('## Pages', $content);
        $this->assertStringContainsString('[About Us]', $content);
        $this->assertStringContainsString(get_permalink($page_id), $content);
    }

    public function test_ignored_post_excluded_from_output() {
        $post_id = self::factory()->post->create(array(
            'post_type'   => 'post',
            'post_title'  => 'Visible Post',
            'post_status' => 'publish',
        ));
        $ignored_id = self::factory()->post->create(array(
            'post_type'   => 'post',
            'post_title'  => 'Hidden Post',
            'post_status' => 'publish',
        ));
        update_post_meta($ignored_id, '_ntllms_txt_builder_ignore_page', '1');
        delete_transient(NT_LLMS_TXT_BUILDER_CACHE_KEY);

        $content = $this->generator->get_llms_txt_content();
        $this->assertStringContainsString('Visible Post', $content);
        $this->assertStringNotContainsString('Hidden Post', $content);
    }

    public function test_full_txt_includes_excerpts() {
        self::factory()->post->create(array(
            'post_type'    => 'post',
            'post_title'   => 'Excerpt Test',
            'post_excerpt' => 'This is a custom excerpt for testing.',
            'post_status'  => 'publish',
        ));
        delete_transient(NT_LLMS_TXT_BUILDER_FULL_CACHE_KEY);

        $content = $this->generator->get_llms_full_txt_content();
        $this->assertStringContainsString('This is a custom excerpt for testing.', $content);
    }

    public function test_standard_txt_omits_excerpts() {
        self::factory()->post->create(array(
            'post_type'    => 'post',
            'post_title'   => 'No Excerpt Post',
            'post_excerpt' => 'Should not appear in standard output.',
            'post_status'  => 'publish',
        ));
        delete_transient(NT_LLMS_TXT_BUILDER_CACHE_KEY);

        $content = $this->generator->get_llms_txt_content();
        $this->assertStringNotContainsString('Should not appear in standard output.', $content);
    }

    public function test_content_is_cached() {
        self::factory()->post->create(array(
            'post_type'   => 'post',
            'post_title'  => 'Cache Test',
            'post_status' => 'publish',
        ));
        delete_transient(NT_LLMS_TXT_BUILDER_CACHE_KEY);

        $first_call = $this->generator->get_llms_txt_content();
        $cached = get_transient(NT_LLMS_TXT_BUILDER_CACHE_KEY);
        $this->assertSame($first_call, $cached);
    }

    public function test_disabled_post_type_not_included() {
        update_option('ntllms_txt_builder_options', array_merge(
            get_option('ntllms_txt_builder_options'),
            array('post_types' => array('post'))
        ));
        self::factory()->post->create(array(
            'post_type'   => 'page',
            'post_title'  => 'Disabled Page',
            'post_status' => 'publish',
        ));
        delete_transient(NT_LLMS_TXT_BUILDER_CACHE_KEY);

        $content = $this->generator->get_llms_txt_content();
        $this->assertStringNotContainsString('Disabled Page', $content);
        $this->assertStringNotContainsString('## Pages', $content);
    }

    public function test_categories_listed_with_names() {
        $cat_id = self::factory()->category->create(array('name' => 'Tech'));
        self::factory()->post->create(array(
            'post_status'   => 'publish',
            'post_category' => array($cat_id),
        ));
        delete_transient(NT_LLMS_TXT_BUILDER_CACHE_KEY);

        $content = $this->generator->get_llms_txt_content();
        $this->assertStringContainsString('## Categories', $content);
        $this->assertStringContainsString('[Tech]', $content);
    }
}
