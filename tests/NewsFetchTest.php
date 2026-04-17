<?php
/**
 * Tests for AANP_News_Fetch — default feeds, caching, date parsing.
 */

use PHPUnit\Framework\TestCase;

class NewsFetchTest extends TestCase {

    private AANP_News_Fetch $fetcher;

    protected function setUp(): void {
        global $_wp_options, $_wp_transients;
        $_wp_options    = array('aanp_settings' => array('rss_feeds' => array()));
        $_wp_transients = array();
        $this->fetcher  = new AANP_News_Fetch();
    }

    public function test_fetch_latest_news_returns_array(): void {
        // HTTP stubs return WP_Error so we get an empty array, not an exception
        $result = $this->fetcher->fetch_latest_news();
        $this->assertIsArray($result);
    }

    public function test_feed_cache_ttl_is_positive(): void {
        $this->assertGreaterThan(0, AANP_News_Fetch::FEED_CACHE_TTL);
    }

    public function test_validate_feed_url_rejects_non_url(): void {
        $this->assertFalse($this->fetcher->validate_feed_url('not-a-url'));
    }

    public function test_validate_feed_url_rejects_unreachable_feed(): void {
        // The HTTP stub always returns a WP_Error so every real URL is "invalid"
        $this->assertFalse($this->fetcher->validate_feed_url('https://feeds.bbci.co.uk/news/rss.xml'));
    }

    public function test_default_feeds_constant_is_populated(): void {
        $this->assertIsArray(AANP_DEFAULT_FEEDS);
        $this->assertNotEmpty(AANP_DEFAULT_FEEDS);
        foreach (AANP_DEFAULT_FEEDS as $feed) {
            $this->assertStringStartsWith('https://', $feed);
        }
    }
}
