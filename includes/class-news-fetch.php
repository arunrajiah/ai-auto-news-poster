<?php
/**
 * News Fetch Class
 *
 * @package AI_Auto_News_Poster
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AANP_News_Fetch {

	/**
	 * Fetch latest news from RSS feeds
	 *
	 * @return array Array of news articles
	 */
	public function fetch_latest_news(): array {
		$options   = get_option( 'aanp_settings', array() );
		$rss_feeds = isset( $options['rss_feeds'] ) ? $options['rss_feeds'] : array();

		if ( empty( $rss_feeds ) ) {
			$rss_feeds = AANP_DEFAULT_FEEDS;
		}

		$articles = array();

		foreach ( $rss_feeds as $feed_url ) {
			$feed_articles = $this->fetch_from_feed( $feed_url );
			if ( ! empty( $feed_articles ) ) {
				$articles = array_merge( $articles, $feed_articles );
			}
		}

		// Sort by publication date (newest first)
		usort(
			$articles,
			function ( $a, $b ) {
				return strtotime( $b['date'] ) - strtotime( $a['date'] );
			}
		);

		// Return top 10 articles
		return array_slice( $articles, 0, 10 );
	}

	/** Cache lifetime in seconds for individual RSS feed responses (30 minutes). */
	const FEED_CACHE_TTL = 1800;

	/**
	 * Build a short, safe transient key for a feed URL.
	 */
	private function feed_cache_key( string $feed_url ): string {
		return 'aanp_feed_' . md5( $feed_url );
	}

	/**
	 * Fetch articles from a single RSS feed, using a transient cache.
	 *
	 * @param string $feed_url RSS feed URL
	 * @return array Array of articles
	 */
	private function fetch_from_feed( string $feed_url ): array {
		$articles = array();

		$cache_key = $this->feed_cache_key( $feed_url );
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		// Use WordPress HTTP API
		$response = wp_remote_get(
			$feed_url,
			array(
				'timeout'    => 30,
				'user-agent' => 'AI Auto News Poster/' . AANP_VERSION,
			)
		);

		if ( is_wp_error( $response ) ) {
			error_log( 'AANP: Failed to fetch RSS feed: ' . $feed_url . ' - ' . $response->get_error_message() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			return $articles;
		}

		$body = wp_remote_retrieve_body( $response );

		if ( '' === $body || empty( $body ) ) {
			error_log( 'AANP: Empty response from RSS feed: ' . $feed_url ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			return $articles;
		}

		// Parse XML
		libxml_use_internal_errors( true );
		$xml = simplexml_load_string( $body );

		if ( false === $xml ) {
			error_log( 'AANP: Failed to parse XML from RSS feed: ' . $feed_url ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			return $articles;
		}

		// Handle different RSS formats
		if ( isset( $xml->channel->item ) ) {
			foreach ( $xml->channel->item as $item ) {
				$article = $this->parse_rss_item( $item, $feed_url );
				if ( $article ) {
					$articles[] = $article;
				}
			}
		} elseif ( isset( $xml->entry ) ) {
			foreach ( $xml->entry as $entry ) {
				$article = $this->parse_atom_entry( $entry, $feed_url );
				if ( $article ) {
					$articles[] = $article;
				}
			}
		}

		// Cache the parsed articles to avoid fetching the same feed on every request
		set_transient( $cache_key, $articles, self::FEED_CACHE_TTL );

		return $articles;
	}

	/**
	 * Parse RSS 2.0 item
	 *
	 * @param SimpleXMLElement $item RSS item
	 * @param string           $feed_url Source feed URL
	 * @return array|null Parsed article data
	 */
	private function parse_rss_item( \SimpleXMLElement $item, string $feed_url ): ?array {
		$title       = (string) $item->title;
		$link        = (string) $item->link;
		$description = (string) $item->description;
		$pub_date    = (string) $item->pubDate; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- RSS property name.

		if ( empty( $title ) || empty( $link ) ) {
			return null;
		}

		// Clean description (remove HTML tags)
		$description = wp_strip_all_tags( $description );
		$description = $this->clean_description( $description );

		// Parse date
		$date = $this->parse_date( $pub_date );

		return array(
			'title'         => $title,
			'link'          => $link,
			'description'   => $description,
			'date'          => $date,
			'source_feed'   => $feed_url,
			'source_domain' => wp_parse_url( $link, PHP_URL_HOST ),
		);
	}

	/**
	 * Parse Atom entry
	 *
	 * @param SimpleXMLElement $entry Atom entry
	 * @param string           $feed_url Source feed URL
	 * @return array|null Parsed article data
	 */
	private function parse_atom_entry( \SimpleXMLElement $entry, string $feed_url ): ?array {
		$title       = (string) $entry->title;
		$link        = '';
		$description = '';
		$pub_date    = (string) $entry->published;

		// Get link
		if ( isset( $entry->link ) ) {
			if ( is_array( $entry->link ) ) {
				foreach ( $entry->link as $link_elem ) {
					if ( 'text/html' === (string) $link_elem['type'] ) {
						$link = (string) $link_elem['href'];
						break;
					}
				}
			} else {
				$link = (string) $entry->link['href'];
			}
		}

		// Get description
		if ( isset( $entry->summary ) ) {
			$description = (string) $entry->summary;
		} elseif ( isset( $entry->content ) ) {
			$description = (string) $entry->content;
		}

		if ( empty( $title ) || empty( $link ) ) {
			return null;
		}

		// Clean description
		$description = wp_strip_all_tags( $description );
		$description = $this->clean_description( $description );

		// Parse date
		$date = $this->parse_date( $pub_date );

		return array(
			'title'         => $title,
			'link'          => $link,
			'description'   => $description,
			'date'          => $date,
			'source_feed'   => $feed_url,
			'source_domain' => wp_parse_url( $link, PHP_URL_HOST ),
		);
	}

	/**
	 * Clean and truncate description
	 *
	 * @param string $description Raw description
	 * @return string Cleaned description
	 */
	private function clean_description( string $description ): string {
		// Remove extra whitespace
		$description = preg_replace( '/\s+/', ' ', $description );
		$description = trim( $description );

		// Truncate to reasonable length for AI processing
		if ( strlen( $description ) > 500 ) {
			$description = substr( $description, 0, 500 ) . '...';
		}

		return $description;
	}

	/**
	 * Parse date string
	 *
	 * @param string $date_string Date string
	 * @return string Formatted date
	 */
	private function parse_date( string $date_string ): string {
		if ( empty( $date_string ) ) {
			return current_time( 'mysql' );
		}

		$timestamp = strtotime( $date_string );

		if ( false === $timestamp ) {
			return current_time( 'mysql' );
		}

		return gmdate( 'Y-m-d H:i:s', $timestamp );
	}

	/**
	 * Get feed info for testing — used internally by validate_feed_url().
	 */
	private function get_feed_info( string $feed_url ): array {
		$response = wp_remote_get(
			$feed_url,
			array(
				'timeout'    => 15,
				'user-agent' => 'AI Auto News Poster/' . AANP_VERSION,
			)
		);

		if ( is_wp_error( $response ) ) {
			return array(
				'status'  => 'error',
				'message' => $response->get_error_message(),
			);
		}

		$body = wp_remote_retrieve_body( $response );
		$xml  = simplexml_load_string( $body );

		if ( false === $xml ) {
			return array(
				'status'  => 'error',
				'message' => 'Invalid XML format',
			);
		}

		$info = array(
			'status'      => 'success',
			'title'       => '',
			'description' => '',
			'item_count'  => 0,
		);

		if ( isset( $xml->channel ) ) {
			// RSS format
			$info['title']       = (string) $xml->channel->title;
			$info['description'] = (string) $xml->channel->description;
			$info['item_count']  = count( $xml->channel->item );
		} elseif ( isset( $xml->title ) ) {
			// Atom format
			$info['title']       = (string) $xml->title;
			$info['description'] = (string) $xml->subtitle;
			$info['item_count']  = count( $xml->entry );
		}

		return $info;
	}

	/**
	 * Validate RSS feed URL
	 *
	 * @param string $url Feed URL
	 * @return bool True if valid
	 */
	public function validate_feed_url( string $url ): bool {
		if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
			return false;
		}

		$info = $this->get_feed_info( $url );
		return 'success' === $info['status'];
	}
}
