<?php
/**
 * Tests for AANP_Admin_Settings — encryption, decryption, and sanitization.
 */

use PHPUnit\Framework\TestCase;

class AdminSettingsTest extends TestCase {

    private AANP_Admin_Settings $settings;

    protected function setUp(): void {
        global $_wp_options, $_wp_settings_errors;
        $_wp_options        = array();
        $_wp_settings_errors = array();
        $this->settings     = new AANP_Admin_Settings();
    }

    public function test_encrypt_returns_enc2_prefix(): void {
        $reflection = new ReflectionMethod(AANP_Admin_Settings::class, 'encrypt_api_key');
        $reflection->setAccessible(true);
        $encrypted = $reflection->invoke($this->settings, 'sk-test-key-1234567890');
        $this->assertStringStartsWith('enc2:', $encrypted);
    }

    public function test_decrypt_round_trips_new_format(): void {
        $reflection = new ReflectionMethod(AANP_Admin_Settings::class, 'encrypt_api_key');
        $reflection->setAccessible(true);
        $original  = 'sk-test-key-1234567890';
        $encrypted = $reflection->invoke($this->settings, $original);
        $decrypted = AANP_Admin_Settings::decrypt_api_key($encrypted);
        $this->assertSame($original, $decrypted);
    }

    public function test_decrypt_empty_returns_empty(): void {
        $this->assertSame('', AANP_Admin_Settings::decrypt_api_key(''));
    }

    public function test_sanitize_preserves_existing_api_key_when_blank_submitted(): void {
        global $_wp_options;
        $_wp_options['aanp_settings'] = array('api_key' => 'enc2:existing-encrypted-key');

        $result = $this->settings->sanitize_settings(array(
            'api_key'      => '',
            'llm_provider' => 'openai',
            'word_count'   => 'medium',
            'tone'         => 'neutral',
            'rss_feeds'    => array('https://example.com/feed.xml'),
        ));

        $this->assertSame('enc2:existing-encrypted-key', $result['api_key']);
    }

    public function test_sanitize_rejects_invalid_provider(): void {
        $result = $this->settings->sanitize_settings(array(
            'llm_provider' => 'evil_provider',
            'word_count'   => 'medium',
            'tone'         => 'neutral',
            'rss_feeds'    => array('https://example.com/feed.xml'),
        ));

        $this->assertSame('openai', $result['llm_provider']);
    }

    public function test_sanitize_rejects_invalid_feeds(): void {
        $result = $this->settings->sanitize_settings(array(
            'llm_provider' => 'openai',
            'word_count'   => 'medium',
            'tone'         => 'neutral',
            'rss_feeds'    => array('not-a-url', 'ftp://bad-scheme.com/feed'),
        ));

        // Falls back to AANP_DEFAULT_FEEDS
        $this->assertSame(AANP_DEFAULT_FEEDS, $result['rss_feeds']);
    }

    public function test_sanitize_accepts_valid_feed(): void {
        $result = $this->settings->sanitize_settings(array(
            'llm_provider' => 'openai',
            'word_count'   => 'medium',
            'tone'         => 'neutral',
            'rss_feeds'    => array('https://feeds.bbci.co.uk/news/rss.xml'),
        ));

        $this->assertContains('https://feeds.bbci.co.uk/news/rss.xml', $result['rss_feeds']);
    }

    public function test_validate_license_key_rejects_short_key(): void {
        $reflection = new ReflectionMethod(AANP_Admin_Settings::class, 'validate_license_key');
        $reflection->setAccessible(true);
        $this->assertFalse($reflection->invoke($this->settings, 'short'));
    }

    public function test_validate_license_key_returns_false_until_server_implemented(): void {
        // License validation now fails closed — any key is rejected until a real
        // license server is wired up. This prevents length-only bypass of Pro features.
        $reflection = new ReflectionMethod(AANP_Admin_Settings::class, 'validate_license_key');
        $reflection->setAccessible(true);
        $this->assertFalse($reflection->invoke($this->settings, 'abcdefghijklmnopqrstu'));
    }

    public function test_rate_limit_constant_is_positive(): void {
        $this->assertGreaterThan(0, AANP_Admin_Settings::RATE_LIMIT_SECONDS);
    }
}
