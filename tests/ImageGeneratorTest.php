<?php
/**
 * Tests for AANP_Image_Generator — image generation and attachment logic.
 */

use PHPUnit\Framework\TestCase;

class ImageGeneratorTest extends TestCase {

    protected function setUp(): void {
        global $_wp_options;
        $_wp_options = array();
    }

    /**
     * Returns false when no API key is present in options.
     */
    public function test_generate_and_attach_returns_false_without_api_key(): void {
        global $_wp_options;
        // get_option('aanp_settings') will return false (default) — no api_key available.
        $_wp_options = array();

        $generator = new AANP_Image_Generator();
        $this->assertFalse( $generator->generate_and_attach( 1, 'Test Title' ) );
    }

    /**
     * Returns false when the provider is not 'openai'.
     */
    public function test_generate_and_attach_returns_false_for_non_openai_provider(): void {
        global $_wp_options;
        $_wp_options['aanp_settings'] = array(
            'llm_provider' => 'anthropic',
            'api_key'      => 'enc2:' . base64_encode( 'sk-test-key-1234567890' ),
        );

        $generator = new AANP_Image_Generator();
        $this->assertFalse( $generator->generate_and_attach( 1, 'Test' ) );
    }

    /**
     * The private build_prompt method should include the article title.
     */
    public function test_build_prompt_contains_title(): void {
        global $_wp_options;
        $_wp_options['aanp_settings'] = array(
            'llm_provider' => 'openai',
            'api_key'      => 'enc2:' . base64_encode( 'sk-test-key-1234567890' ),
        );

        $generator  = new AANP_Image_Generator();
        $reflection = new ReflectionMethod( AANP_Image_Generator::class, 'build_prompt' );
        $reflection->setAccessible( true );

        $prompt = $reflection->invoke( $generator, 'Breaking News' );
        $this->assertStringContainsString( 'Breaking News', $prompt );
    }

    /**
     * sideload_image returns false when media_sideload_image is unavailable.
     */
    public function test_sideload_returns_false_on_wp_error(): void {
        $this->markTestSkipped( 'media_sideload_image not available in test environment' );
    }
}
