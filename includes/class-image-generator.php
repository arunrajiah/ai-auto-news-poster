<?php
/**
 * Image Generator Class
 *
 * Generates featured images via the OpenAI DALL-E API and attaches them to
 * WordPress posts.
 *
 * @package AI_Auto_News_Poster
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AANP_Image_Generator {

	/**
	 * OpenAI API key.
	 *
	 * @var string
	 */
	private string $api_key;

	/**
	 * LLM provider slug.
	 *
	 * @var string
	 */
	private string $provider;

	/**
	 * Constructor — reads settings and decrypts the stored API key.
	 */
	public function __construct() {
		$options        = get_option( 'aanp_settings', array() );
		$encrypted_key  = $options['api_key'] ?? '';
		$this->api_key  = AANP_Admin_Settings::decrypt_api_key( $encrypted_key );
		$this->provider = $options['llm_provider'] ?? 'openai';
	}

	/**
	 * Generate a featured image via DALL-E and attach it to a post.
	 *
	 * @param int    $post_id WordPress post ID.
	 * @param string $title   Post title used to build the image prompt.
	 * @return bool True on success, false on any failure.
	 */
	public function generate_and_attach( int $post_id, string $title ): bool {
		if ( 'openai' !== $this->provider || empty( $this->api_key ) ) {
			return false;
		}

		$prompt    = $this->build_prompt( $title );
		$image_url = $this->generate_with_dalle( $prompt );

		if ( null === $image_url ) {
			return false;
		}

		$attachment_id = $this->sideload_image( $image_url, $post_id, $title );

		if ( false === $attachment_id ) {
			return false;
		}

		return (bool) set_post_thumbnail( $post_id, $attachment_id );
	}

	/**
	 * Build a DALL-E prompt from the article title.
	 *
	 * @param string $title Post title.
	 * @return string Prompt string.
	 */
	private function build_prompt( string $title ): string {
		return 'Create a professional, photorealistic editorial image for a news article titled: "' . wp_strip_all_tags( $title ) . '". No text or watermarks. Suitable for a blog post featured image.';
	}

	/**
	 * Call the OpenAI Images API and return the generated image URL.
	 *
	 * @param string $prompt Image generation prompt.
	 * @return string|null Image URL on success, null on failure.
	 */
	private function generate_with_dalle( string $prompt ): ?string {
		$response = wp_remote_post(
			'https://api.openai.com/v1/images/generations',
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $this->api_key,
					'Content-Type'  => 'application/json',
				),
				'body'    => wp_json_encode(
					array(
						'model'  => 'dall-e-3',
						'prompt' => $prompt,
						'n'      => 1,
						'size'   => '1792x1024',
					)
				),
				'timeout' => 60,
			)
		);

		if ( is_wp_error( $response ) ) {
			error_log( 'AANP_Image_Generator: DALL-E request failed — ' . $response->get_error_message() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			return null;
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( isset( $data['data'][0]['url'] ) ) {
			return $data['data'][0]['url'];
		}

		error_log( 'AANP_Image_Generator: Unexpected DALL-E response — ' . $body ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		return null;
	}

	/**
	 * Sideload a remote image into the WordPress media library.
	 *
	 * @param string $image_url Remote image URL.
	 * @param int    $post_id   Post the attachment will belong to.
	 * @param string $title     Attachment title / alt text.
	 * @return int|false Attachment ID on success, false on failure.
	 */
	private function sideload_image( string $image_url, int $post_id, string $title ) {
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		$attachment_id = media_sideload_image( $image_url, $post_id, $title, 'id' );

		if ( is_wp_error( $attachment_id ) ) {
			error_log( 'AANP_Image_Generator: media_sideload_image failed — ' . $attachment_id->get_error_message() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			return false;
		}

		return (int) $attachment_id;
	}
}
