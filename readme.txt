=== AI Auto News Poster ===
Contributors: arunrajiah
Tags: ai, news, auto-posting, content generation, rss, openai, anthropic
Requires at least: 5.0
Tested up to: 6.4
Stable tag: 1.0.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Auto-generate unique blog posts from the latest news using AI. Free users can generate up to 5 posts manually per batch.

== Description ==

AI Auto News Poster is a powerful WordPress plugin that automatically transforms the latest news articles into unique, engaging blog posts using advanced AI technology. Perfect for news websites, blogs, and content creators who want to stay current with trending topics.

**Key Features:**

* **AI-Powered Content Generation**: Uses OpenAI, Anthropic, or custom APIs to create unique blog posts
* **RSS Feed Integration**: Fetches latest news from popular sources or custom RSS feeds
* **Manual Batch Generation**: Generate up to 5 unique posts with one click (free version)
* **Customizable Settings**: Choose tone, word count, and post categories
* **Draft Posts**: All generated content is saved as drafts for review before publishing
* **Source Attribution**: Proper attribution to original news sources
* **Security First**: Secure API key storage and input sanitization

**How It Works:**

1. Configure your AI provider (OpenAI, Anthropic, or Custom API)
2. Add RSS feeds from your favorite news sources
3. Select categories and content preferences
4. Click "Generate 5 Posts" to create unique blog content
5. Review and publish the generated drafts

**Free Version Includes:**
* Manual generation of up to 5 posts per batch
* Support for OpenAI and Anthropic APIs
* Custom RSS feed management
* Basic content customization
* Source attribution

**Pro Features (Coming Soon):**
* Automated scheduling with WP-Cron
* Generate up to 30 posts per batch
* Automatic featured image generation
* SEO meta tags auto-fill
* Priority support

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/ai-auto-news-poster` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Go to Settings > AI Auto News Poster to configure the plugin
4. Add your AI API key and configure your preferences
5. Add RSS feeds for news sources
6. Click "Generate 5 Posts" to start creating content

== Frequently Asked Questions ==

= What AI providers are supported? =

Currently, the plugin supports OpenAI (GPT models) and Anthropic (Claude models). A custom API option is also available for advanced users.

= Do I need an API key? =

Yes, you need an API key from your chosen AI provider (OpenAI or Anthropic). The plugin does not include AI credits.

= Are the generated posts unique? =

Yes, the AI is instructed to create unique content based on news headlines and summaries, not to copy directly from sources.

= Can I customize the generated content? =

Yes, you can choose the tone (neutral, professional, friendly), word count (short, medium, long), and post categories.

= Are posts published automatically? =

No, all generated posts are saved as drafts. You can review and edit them before publishing.

= How many posts can I generate? =

The free version allows up to 5 posts per batch. Pro version (coming soon) will support up to 30 posts per batch.

= Is the plugin secure? =

Yes, the plugin follows WordPress security best practices including input sanitization, nonce verification, and secure API key storage.

== Screenshots ==

1. Main settings page with AI configuration
2. RSS feed management
3. Post generation interface
4. Generated posts dashboard
5. Pro features preview

== Changelog ==

= 1.0.0 =
* Initial release
* AI-powered content generation with OpenAI and Anthropic support
* RSS feed integration
* Manual batch generation (5 posts)
* Customizable tone and word count
* Draft post creation with source attribution
* Security features and input sanitization
* Pro features placeholder

== Upgrade Notice ==

= 1.0.0 =
Initial release of AI Auto News Poster. Configure your AI provider and start generating unique blog posts from the latest news!

== Support ==

For support, feature requests, or bug reports:
* GitHub Issues: https://github.com/arunrajiah/ai-auto-news-poster/issues
* Email: Contact via GitHub profile
* Documentation: https://github.com/arunrajiah/ai-auto-news-poster/wiki

== Privacy Policy ==

This plugin sends article headlines and summaries to your configured AI provider (OpenAI, Anthropic, or custom API) for content generation. Please review your AI provider's privacy policy and terms of service. 

**Data Handling:**
* API keys are encrypted and stored securely in WordPress options
* No personal user data is transmitted to AI providers
* Generated content is stored locally in your WordPress database
* RSS feed URLs are stored in plugin settings
* Plugin logs errors locally for debugging purposes

== Third-Party Services ==

This plugin integrates with external services:

**AI Providers:**
* OpenAI API (https://openai.com/policies/privacy-policy)
* Anthropic API (https://www.anthropic.com/privacy)

**RSS Feeds:**
* BBC News (https://www.bbc.com/privacy)
* CNN (https://www.cnn.com/privacy)
* Reuters (https://www.reuters.com/privacy-policy)
* Custom RSS feeds as configured by user

== Credits ==

Developed by Arun Rajiah
* GitHub: https://github.com/arunrajiah
* Plugin Repository: https://github.com/arunrajiah/ai-auto-news-poster

**Technologies Used:**
* WordPress HTTP API for RSS feed fetching
* OpenAI GPT models for content generation
* Anthropic Claude models for content generation
* WordPress Settings API for configuration
* WordPress AJAX for dynamic interactions

== Contributing ==

Contributions are welcome! Please visit our GitHub repository to:
* Report bugs
* Suggest features
* Submit pull requests
* Improve documentation

== License ==

This plugin is licensed under GPL v2 or later. You are free to use, modify, and distribute this plugin according to the terms of the GPL license.
