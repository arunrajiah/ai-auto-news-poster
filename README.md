# AI Auto News Poster

![CI](https://github.com/arunrajiah/ai-auto-news-poster/actions/workflows/ci.yml/badge.svg)
![Version](https://img.shields.io/badge/Version-1.0.6-green.svg)
![License](https://img.shields.io/badge/License-GPL%20v2-blue.svg)
![PHP](https://img.shields.io/badge/PHP-7.4%2B-purple.svg)
![WordPress](https://img.shields.io/badge/WordPress-5.0%2B-blue.svg)

AI-powered WordPress plugin that automatically generates unique blog posts from the latest news using OpenAI, Anthropic Claude, or any OpenAI-compatible API. Features RSS feed integration with transient caching, per-article AJAX generation with live progress feedback, duplicate detection, and AES-256 encrypted API key storage.

## Features

### Free Version
- **Multi-provider AI generation** — OpenAI GPT-3.5-turbo, Anthropic Claude, or any OpenAI-compatible custom endpoint
- **RSS feed management** — add/remove/test feeds; parsed results cached for 30 minutes via WordPress transients
- **Batch post creation** — generate up to 5 unique blog posts per batch
- **Live progress UI** — per-article status with progress bar and cooldown timer (60-second rate limit)
- **Duplicate detection** — articles already posted are skipped automatically
- **Customisable content** — configure tone of voice, word count, and post categories
- **AES-256 encrypted API keys** — keys are encrypted with a per-install key derived from `wp_salt('auth')` and never rendered back into the page
- **Draft-first workflow** — all posts saved as drafts for review before publishing
- **Source attribution** — each post links back to its original news source

### Pro Features (Coming Soon)
- Automated scheduling via WP-Cron
- Up to 30 posts per batch
- AI-powered featured image generation
- SEO meta tags auto-fill
- Priority support

## Requirements

| Component | Minimum |
|-----------|---------|
| WordPress | 5.0 |
| PHP | 7.4 |
| PHP extensions | `openssl`, `simplexml`, `mbstring` |
| Database | MySQL 5.6 / MariaDB 10.1 |
| AI API key | OpenAI, Anthropic, or compatible |

## Installation

### WordPress Admin (Recommended)
1. Download the plugin zip from the [releases page](https://github.com/arunrajiah/ai-auto-news-poster/releases)
2. Go to **Plugins > Add New > Upload Plugin**
3. Select the zip, click **Install Now**, then **Activate**

### Manual
1. Upload the `ai-auto-news-poster` folder to `/wp-content/plugins/`
2. Activate from the **Plugins** screen

### Developer (Git)
```bash
git clone https://github.com/arunrajiah/ai-auto-news-poster.git
cp -r ai-auto-news-poster /path/to/wordpress/wp-content/plugins/
```

## Configuration

Navigate to **Settings > AI Auto News Poster** after activation.

| Setting | Description |
|---------|-------------|
| LLM Provider | OpenAI, Anthropic, or Custom API |
| API Key | Encrypted with AES-256-CBC on save; never shown again |
| Custom API Endpoint | Any OpenAI-compatible URL (only for Custom API) |
| Custom API Model | Model name sent in the request body |
| Post Categories | WordPress categories assigned to generated posts |
| Word Count | Short (300-400 w), Medium (500-600 w), Long (800-1000 w) |
| Tone of Voice | Neutral, Professional, or Friendly |
| RSS Feed URLs | One URL per row; use the **Test** button to validate |
| Pro License Key | Unlocks Pro features when available |

### Getting API Keys

**OpenAI** — [platform.openai.com](https://platform.openai.com/) → API Keys → Create new secret key

**Anthropic** — [console.anthropic.com](https://console.anthropic.com/) → API Keys → Create key

## Usage

### Generating Posts
1. Go to **Settings > AI Auto News Poster**
2. Click **Generate 5 Posts**
3. The plugin fetches articles from your RSS feeds, then generates one post at a time with a live progress indicator
4. Each post appears in the results list with an edit link as soon as it is created
5. Review and publish drafts from **Posts > All Posts**

### Managing RSS Feeds
- Click **Add RSS Feed** to add a new row
- Click **Test** next to any feed URL to validate it before saving
- Parsed feed results are cached for 30 minutes; clear the WordPress transient cache to force a refresh

### Duplicate Detection
The plugin tracks every generated post's source URL in a custom database table (`wp_aanp_generated_posts`). If the same article URL is fetched again it is silently skipped.

## Security

| Feature | Detail |
|---------|--------|
| API key encryption | AES-256-CBC; key = `substr(sha256(wp_salt('auth')), 0, 32)` |
| API key display | Stored value is never echoed; a placeholder is shown instead |
| AJAX nonce | Every AJAX request verified with `wp_verify_nonce()` |
| Capability check | `manage_options` required for all admin actions |
| SQL | `$wpdb->prepare()` for all parametrised queries |
| Output escaping | `esc_html()`, `esc_attr()`, `esc_url()` throughout |
| Rate limiting | 60-second cooldown between generation requests (stored in a transient) |
| Input sanitisation | Settings sanitised and validated before save |

## Architecture

```
ai-auto-news-poster/
├── ai-auto-news-poster.php          # Plugin bootstrap; constants; DB migrations
├── includes/
│   ├── class-admin-settings.php     # Settings API, AJAX handlers, encryption
│   ├── class-news-fetch.php         # RSS/Atom parsing with transient cache
│   ├── class-ai-generator.php       # OpenAI / Anthropic / Custom API calls
│   ├── class-post-creator.php       # WP post creation, duplicate check, stats
│   └── class-pro-features.php       # Pro feature stubs / upgrade notices
├── admin/
│   └── settings-page.php            # Admin page template
├── assets/
│   ├── css/admin.css                # Admin styles (no inline CSS)
│   └── js/admin.js                  # Phase-based AJAX generation flow
├── tests/
│   ├── bootstrap.php                # PHPUnit bootstrap
│   ├── stubs.php                    # WordPress function stubs
│   ├── AdminSettingsTest.php        # Encryption, sanitisation, rate-limit tests
│   ├── PostCreatorTest.php          # Duplicate detection, post creation tests
│   ├── NewsFetchTest.php            # Feed URL validation, cache TTL tests
│   └── AiGeneratorTest.php          # Prompt building, response parsing tests
├── .github/workflows/ci.yml         # CI: PHP lint, PHPCS, PHPUnit
├── .phpcs.xml                       # WordPress coding-standard ruleset
├── composer.json                    # Dev dependencies
├── phpunit.xml                      # PHPUnit config
├── readme.txt                       # WordPress.org repository readme
└── README.md                        # This file
```

### AJAX Generation Flow

The JavaScript uses a two-phase approach to show real-time progress:

1. **Phase 1** — calls `aanp_fetch_articles` to retrieve up to 5 candidate articles from RSS feeds
2. **Phase 2** — calls `aanp_generate_single` once per article, sequentially, updating a progress bar after each

This avoids a single slow HTTP request and lets the user see each post appear as soon as it is created.

## Development

### Prerequisites
```bash
composer install   # installs phpunit, phpcs, wpcs
```

### Commands
| Command | Description |
|---------|-------------|
| `composer test` | Run PHPUnit (requires PHP 8.1+) |
| `composer lint` | Run PHPCS against WordPress coding standards |
| `composer lint-fix` | Auto-fix PHPCS violations with phpcbf |

### CI Pipeline

Three GitHub Actions jobs run on every push to `main`/`develop`:

| Job | PHP versions | Tool |
|-----|-------------|------|
| PHP Syntax Check | 7.4, 8.0, 8.1, 8.2, 8.3 | `php -l` |
| WordPress Coding Standards | 8.2 | PHPCS + WPCS |
| PHPUnit Tests | 8.1, 8.2, 8.3 | PHPUnit 10 |

> PHPUnit 10 requires PHP ≥ 8.1. Syntax checking still validates PHP 7.4 and 8.0 compatibility via the lint job.

### Coding Standards

The project follows [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/) enforced by PHPCS with the `WordPress-Core` and `WordPress-Extra` rule sets. Key rules applied:

- Tabs for indentation
- Yoda conditions (`null === $var`)
- `wp_json_encode()`, `wp_parse_url()`, `wp_strip_all_tags()` over PHP equivalents
- `esc_html__()` / `esc_html_e()` rather than bare `__()`
- `gmdate()` instead of `date()`
- `$wpdb->prepare()` for all SQL with user-controlled values

## API Integration

### OpenAI
- **Model**: `gpt-3.5-turbo`
- **Endpoint**: `https://api.openai.com/v1/chat/completions`
- **Auth**: `Authorization: Bearer <key>`

### Anthropic
- **Model**: `claude-3-sonnet-20240229`
- **Endpoint**: `https://api.anthropic.com/v1/messages`
- **Auth**: `x-api-key: <key>` + `anthropic-version: 2023-06-01`

### Custom API
- Any OpenAI-compatible endpoint (e.g. Ollama, LM Studio, OpenRouter)
- Model name is configurable; falls back to `"default"` if blank
- API key is optional (sent as `Authorization: Bearer <key>` when present)

All providers expect the response to contain a JSON object with `title` and `content` fields. If JSON parsing fails the plugin attempts plain-text extraction and, as a last resort, generates a minimal fallback post from the original article data.

## Troubleshooting

**Posts not generating**
- Check the API key is correct and the account has credits
- Enable `WP_DEBUG_LOG` and inspect `wp-content/debug.log` for `AANP:` entries
- Use the **Test** button next to each RSS feed to confirm the feed is reachable

**Rate limit message**
- The plugin enforces a 60-second cooldown between batches. Wait and try again.

**Duplicate articles skipped**
- Check `wp_aanp_generated_posts` in your database — the source URL is already recorded. Delete the row to allow re-generation.

**API errors**
- Verify the API key, check your provider's status page, and confirm outbound HTTPS is not blocked by a firewall or proxy.

**Debug mode**
```php
// wp-config.php
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
```

## Contributing

1. Fork the repository
2. Create a branch: `git checkout -b feature/my-feature`
3. Make changes, run `composer test` and `composer lint`
4. Push and open a Pull Request against `main`

Please follow WordPress Coding Standards and include tests for new functionality.

## Changelog

### 1.0.6
- Fixed all WordPress Plugin Check errors (i18n, escaping, missing translators comments)
- Inline styles removed from admin templates; moved to `admin.css`
- Added semantic CSS classes for stat boxes and status indicators
- Fixed `readme.txt` stable tag

### 1.0.5
- AES-256-CBC API key encryption with `wp_salt('auth')`-derived key (replaces plaintext storage)
- Rate limiting: 60-second cooldown between generation batches
- Per-article AJAX generation with live progress bar (`aanp_fetch_articles` + `aanp_generate_single`)
- Feed URL **Test** button via `aanp_test_feed` AJAX action
- Custom API endpoint and model name settings
- Pro license key field with active/inactive badge
- Duplicate post detection via `wp_aanp_generated_posts` tracking table with post meta fallback
- RSS feed transient cache (30-minute TTL, `AANP_DEFAULT_FEEDS` constant)
- PHP 7.4 type hints across all classes
- PHPUnit test suite (31 tests) and GitHub Actions CI pipeline
- WordPress Coding Standards (PHPCS) enforced in CI

### 1.0.3 – 1.0.4
- Fixed WordPress i18n `NonSingularStringLiteralText` errors
- `readme.txt` stable tag corrections

### 1.0.0
- Initial release: OpenAI and Anthropic integration, RSS feed parsing, batch draft creation, admin settings UI

## License

GPL v2 or later — see [LICENSE](https://www.gnu.org/licenses/gpl-2.0.html).

## Author

**Arun Rajiah** — [github.com/arunrajiah](https://github.com/arunrajiah)

## Support

- Bug reports / feature requests: [GitHub Issues](https://github.com/arunrajiah/ai-auto-news-poster/issues)
- Documentation: this README and inline code comments
