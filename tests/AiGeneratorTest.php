<?php
/**
 * Tests for AANP_AI_Generator — prompt building and response parsing.
 */

use PHPUnit\Framework\TestCase;

class AiGeneratorTest extends TestCase {

    private AANP_AI_Generator $generator;

    protected function setUp(): void {
        global $_wp_options;
        $_wp_options['aanp_settings'] = array(
            'llm_provider' => 'openai',
            'api_key'      => '',
            'word_count'   => 'medium',
            'tone'         => 'neutral',
        );
        $this->generator = new AANP_AI_Generator();
    }

    private function sampleArticle(): array {
        return array(
            'title'         => 'Test Headline',
            'description'   => 'A short description of the event.',
            'link'          => 'https://example.com/article',
            'source_domain' => 'example.com',
        );
    }

    public function test_generate_content_returns_false_when_no_api_key(): void {
        $result = $this->generator->generate_content($this->sampleArticle());
        $this->assertFalse($result);
    }

    public function test_test_api_connection_returns_array_with_status(): void {
        $result = $this->generator->test_api_connection();
        $this->assertIsArray($result);
        $this->assertArrayHasKey('status', $result);
    }

    public function test_build_prompt_includes_article_title(): void {
        $reflection = new ReflectionMethod(AANP_AI_Generator::class, 'build_prompt');
        $reflection->setAccessible(true);
        $article = $this->sampleArticle();
        $prompt  = $reflection->invoke($this->generator, $article);
        $this->assertStringContainsString($article['title'], $prompt);
    }

    public function test_build_prompt_includes_word_range(): void {
        $reflection = new ReflectionMethod(AANP_AI_Generator::class, 'build_prompt');
        $reflection->setAccessible(true);
        $prompt = $reflection->invoke($this->generator, $this->sampleArticle());
        $this->assertStringContainsString('500-600', $prompt, 'Medium word count range should appear in prompt');
    }

    public function test_parse_ai_response_parses_valid_json(): void {
        $reflection = new ReflectionMethod(AANP_AI_Generator::class, 'parse_ai_response');
        $reflection->setAccessible(true);

        $json    = json_encode(array(
            'title'   => 'Generated Title',
            'content' => 'Generated content that is long enough to pass any validator.',
        ));
        $article = $this->sampleArticle();
        $result  = $reflection->invoke($this->generator, $json, $article);

        $this->assertIsArray($result);
        $this->assertSame('Generated Title', $result['title']);
        $this->assertArrayHasKey('content', $result);
    }

    public function test_parse_ai_response_falls_back_gracefully_on_invalid_json(): void {
        $reflection = new ReflectionMethod(AANP_AI_Generator::class, 'parse_ai_response');
        $reflection->setAccessible(true);

        $result = $reflection->invoke($this->generator, 'this is not json at all...', $this->sampleArticle());

        // Should still return something (either parsed or fallback)
        $this->assertIsArray($result);
        $this->assertArrayHasKey('title', $result);
    }

    public function test_sanitize_for_log_truncates_long_string(): void {
        $reflection = new ReflectionMethod(AANP_AI_Generator::class, 'sanitize_for_log');
        $reflection->setAccessible(true);

        $long   = str_repeat('a', 500);
        $result = $reflection->invoke($this->generator, $long, 200);

        $this->assertLessThanOrEqual(200, strlen($result));
    }
}
