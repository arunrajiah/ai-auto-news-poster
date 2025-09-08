<?php
/**
 * News Fetch Class
 *
 * @package AI_Auto_News_Poster
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class AANP_News_Fetch {
    
    /**
     * Fetch latest news from RSS feeds
     *
     * @return array Array of news articles
     */
    public function fetch_latest_news() {
        $options = get_option('aanp_settings', array());
        $rss_feeds = isset($options['rss_feeds']) ? $options['rss_feeds'] : array();
        
        if (empty($rss_feeds)) {
            // Use default feeds if none configured
            $rss_feeds = array(
                'https://feeds.bbci.co.uk/news/rss.xml',
                'https://rss.cnn.com/rss/edition.rss',
                'https://feeds.reuters.com/reuters/topNews'
            );
        }
        
        $articles = array();
        
        foreach ($rss_feeds as $feed_url) {
            $feed_articles = $this->fetch_from_feed($feed_url);
            if (!empty($feed_articles)) {
                $articles = array_merge($articles, $feed_articles);
            }
        }
        
        // Sort by publication date (newest first)
        usort($articles, function($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });
        
        // Return top 10 articles
        return array_slice($articles, 0, 10);
    }
    
    /**
     * Fetch articles from a single RSS feed
     *
     * @param string $feed_url RSS feed URL
     * @return array Array of articles
     */
    private function fetch_from_feed($feed_url) {
        $articles = array();
        
        // Use WordPress HTTP API
        $response = wp_remote_get($feed_url, array(
            'timeout' => 30,
            'user-agent' => 'AI Auto News Poster/' . AANP_VERSION
        ));
        
        if (is_wp_error($response)) {
            error_log('AANP: Failed to fetch RSS feed: ' . $feed_url . ' - ' . $response->get_error_message());
            return $articles;
        }
        
        $body = wp_remote_retrieve_body($response);
        
        if (empty($body)) {
            error_log('AANP: Empty response from RSS feed: ' . $feed_url);
            return $articles;
        }
        
        // Parse XML
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($body);
        
        if ($xml === false) {
            error_log('AANP: Failed to parse XML from RSS feed: ' . $feed_url);
            return $articles;
        }
        
        // Handle different RSS formats
        if (isset($xml->channel->item)) {
            // RSS 2.0 format
            foreach ($xml->channel->item as $item) {
                $article = $this->parse_rss_item($item, $feed_url);
                if ($article) {
                    $articles[] = $article;
                }
            }
        } elseif (isset($xml->entry)) {
            // Atom format
            foreach ($xml->entry as $entry) {
                $article = $this->parse_atom_entry($entry, $feed_url);
                if ($article) {
                    $articles[] = $article;
                }
            }
        }
        
        return $articles;
    }
    
    /**
     * Parse RSS 2.0 item
     *
     * @param SimpleXMLElement $item RSS item
     * @param string $feed_url Source feed URL
     * @return array|null Parsed article data
     */
    private function parse_rss_item($item, $feed_url) {
        $title = (string) $item->title;
        $link = (string) $item->link;
        $description = (string) $item->description;
        $pub_date = (string) $item->pubDate;
        
        if (empty($title) || empty($link)) {
            return null;
        }
        
        // Clean description (remove HTML tags)
        $description = wp_strip_all_tags($description);
        $description = $this->clean_description($description);
        
        // Parse date
        $date = $this->parse_date($pub_date);
        
        return array(
            'title' => $title,
            'link' => $link,
            'description' => $description,
            'date' => $date,
            'source_feed' => $feed_url,
            'source_domain' => parse_url($link, PHP_URL_HOST)
        );
    }
    
    /**
     * Parse Atom entry
     *
     * @param SimpleXMLElement $entry Atom entry
     * @param string $feed_url Source feed URL
     * @return array|null Parsed article data
     */
    private function parse_atom_entry($entry, $feed_url) {
        $title = (string) $entry->title;
        $link = '';
        $description = '';
        $pub_date = (string) $entry->published;
        
        // Get link
        if (isset($entry->link)) {
            if (is_array($entry->link)) {
                foreach ($entry->link as $link_elem) {
                    if ((string) $link_elem['type'] === 'text/html') {
                        $link = (string) $link_elem['href'];
                        break;
                    }
                }
            } else {
                $link = (string) $entry->link['href'];
            }
        }
        
        // Get description
        if (isset($entry->summary)) {
            $description = (string) $entry->summary;
        } elseif (isset($entry->content)) {
            $description = (string) $entry->content;
        }
        
        if (empty($title) || empty($link)) {
            return null;
        }
        
        // Clean description
        $description = wp_strip_all_tags($description);
        $description = $this->clean_description($description);
        
        // Parse date
        $date = $this->parse_date($pub_date);
        
        return array(
            'title' => $title,
            'link' => $link,
            'description' => $description,
            'date' => $date,
            'source_feed' => $feed_url,
            'source_domain' => parse_url($link, PHP_URL_HOST)
        );
    }
    
    /**
     * Clean and truncate description
     *
     * @param string $description Raw description
     * @return string Cleaned description
     */
    private function clean_description($description) {
        // Remove extra whitespace
        $description = preg_replace('/\s+/', ' ', $description);
        $description = trim($description);
        
        // Truncate to reasonable length for AI processing
        if (strlen($description) > 500) {
            $description = substr($description, 0, 500) . '...';
        }
        
        return $description;
    }
    
    /**
     * Parse date string
     *
     * @param string $date_string Date string
     * @return string Formatted date
     */
    private function parse_date($date_string) {
        if (empty($date_string)) {
            return current_time('mysql');
        }
        
        $timestamp = strtotime($date_string);
        
        if ($timestamp === false) {
            return current_time('mysql');
        }
        
        return date('Y-m-d H:i:s', $timestamp);
    }
    
    /**
     * Get feed info for testing
     *
     * @param string $feed_url RSS feed URL
     * @return array Feed information
     */
    public function get_feed_info($feed_url) {
        $response = wp_remote_get($feed_url, array(
            'timeout' => 15,
            'user-agent' => 'AI Auto News Poster/' . AANP_VERSION
        ));
        
        if (is_wp_error($response)) {
            return array(
                'status' => 'error',
                'message' => $response->get_error_message()
            );
        }
        
        $body = wp_remote_retrieve_body($response);
        $xml = simplexml_load_string($body);
        
        if ($xml === false) {
            return array(
                'status' => 'error',
                'message' => 'Invalid XML format'
            );
        }
        
        $info = array(
            'status' => 'success',
            'title' => '',
            'description' => '',
            'item_count' => 0
        );
        
        if (isset($xml->channel)) {
            // RSS format
            $info['title'] = (string) $xml->channel->title;
            $info['description'] = (string) $xml->channel->description;
            $info['item_count'] = count($xml->channel->item);
        } elseif (isset($xml->title)) {
            // Atom format
            $info['title'] = (string) $xml->title;
            $info['description'] = (string) $xml->subtitle;
            $info['item_count'] = count($xml->entry);
        }
        
        return $info;
    }
    
    /**
     * Validate RSS feed URL
     *
     * @param string $url Feed URL
     * @return bool True if valid
     */
    public function validate_feed_url($url) {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }
        
        $info = $this->get_feed_info($url);
        return $info['status'] === 'success';
    }
}
