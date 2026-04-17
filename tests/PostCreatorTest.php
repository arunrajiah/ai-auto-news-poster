<?php
/**
 * Tests for AANP_Post_Creator — validation, deduplication, and post creation.
 */

use PHPUnit\Framework\TestCase;

class PostCreatorTest extends TestCase {

    private AANP_Post_Creator $creator;

    protected function setUp(): void {
        global $_wp_posts, $_wp_post_meta, $_wp_transients;
        $_wp_posts     = array();
        $_wp_post_meta = array();
        $_wp_transients = array();
        $this->creator = new AANP_Post_Creator();
    }

    private function validContent(): array {
        return array(
            'title'         => 'Test Post Title',
            'content'       => str_repeat('Lorem ipsum dolor sit amet. ', 10),
            'source_url'    => 'https://example.com/article',
            'source_domain' => 'example.com',
        );
    }

    private function validArticle(): array {
        return array(
            'title'         => 'Original Article',
            'link'          => 'https://example.com/article',
            'description'   => 'Some description',
            'date'          => date('Y-m-d H:i:s'),
            'source_feed'   => 'https://example.com/feed.xml',
            'source_domain' => 'example.com',
        );
    }

    public function test_validate_passes_for_valid_data(): void {
        $result = $this->creator->validate_post_data($this->validContent(), $this->validArticle());
        $this->assertTrue($result['valid']);
        $this->assertEmpty($result['errors']);
    }

    public function test_validate_fails_for_empty_title(): void {
        $content          = $this->validContent();
        $content['title'] = '';
        $result           = $this->creator->validate_post_data($content, $this->validArticle());
        $this->assertFalse($result['valid']);
        $this->assertNotEmpty($result['errors']);
    }

    public function test_validate_fails_for_short_content(): void {
        $content            = $this->validContent();
        $content['content'] = 'Too short';
        $result             = $this->creator->validate_post_data($content, $this->validArticle());
        $this->assertFalse($result['valid']);
    }

    public function test_validate_fails_for_invalid_source_url(): void {
        $article         = $this->validArticle();
        $article['link'] = 'not-a-url';
        $result          = $this->creator->validate_post_data($this->validContent(), $article);
        $this->assertFalse($result['valid']);
    }

    public function test_is_duplicate_returns_false_for_new_url(): void {
        $this->assertFalse($this->creator->is_duplicate('https://example.com/new-article'));
    }

    public function test_create_post_returns_integer(): void {
        $post_id = $this->creator->create_post($this->validContent(), $this->validArticle());
        $this->assertIsInt($post_id);
        $this->assertGreaterThan(0, $post_id);
    }

    public function test_create_post_returns_false_for_empty_title(): void {
        $content          = $this->validContent();
        $content['title'] = '';
        $result           = $this->creator->create_post($content, $this->validArticle());
        $this->assertFalse($result);
    }

    public function test_get_stats_returns_expected_keys(): void {
        $stats = $this->creator->get_stats();
        $this->assertArrayHasKey('total', $stats);
        $this->assertArrayHasKey('today', $stats);
        $this->assertArrayHasKey('week', $stats);
        $this->assertArrayHasKey('month', $stats);
    }

    public function test_validate_post_data_title_too_long(): void {
        $content          = $this->validContent();
        $content['title'] = str_repeat('A', 201);
        $result           = $this->creator->validate_post_data($content, $this->validArticle());
        $this->assertFalse($result['valid']);
    }
}
