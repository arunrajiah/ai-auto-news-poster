<?php
/**
 * Pro Features Class
 *
 * All features are available to all users. This class is retained for
 * extensibility and fires action hooks that third-party code can hook into.
 *
 * @package AI_Auto_News_Poster
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AANP_Pro_Features {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'init_features' ) );
	}

	/**
	 * Initialize features and register action hooks.
	 */
	public function init_features() {
		add_action( 'aanp_after_post_generation', array( $this, 'generate_featured_image' ), 10, 2 );
		add_action( 'aanp_after_post_creation', array( $this, 'add_seo_meta' ), 10, 2 );
	}

	/**
	 * Generate featured image for post.
	 *
	 * @param int   $post_id Post ID.
	 * @param array $article Article data.
	 */
	public function generate_featured_image( $post_id, $article ) {
		do_action( 'aanp_generate_featured_image', $post_id, $article );
	}

	/**
	 * Add SEO meta tags.
	 *
	 * @param int   $post_id          Post ID.
	 * @param array $generated_content Generated content.
	 */
	public function add_seo_meta( $post_id, $generated_content ) {
		do_action( 'aanp_add_seo_meta', $post_id, $generated_content );
	}
}

// Initialize features.
new AANP_Pro_Features();
