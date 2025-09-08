# AI Auto News Poster

![WordPress Plugin](https://img.shields.io/badge/WordPress-Plugin-blue.svg)
![Version](https://img.shields.io/badge/Version-1.0.0-green.svg)
![License](https://img.shields.io/badge/License-GPL%20v2-blue.svg)
![PHP](https://img.shields.io/badge/PHP-7.4%2B-purple.svg)

AI-powered WordPress plugin that automatically generates unique blog posts from the latest news using OpenAI and Anthropic APIs. Features RSS feed integration, customizable content generation, and batch post creation with security-first design.

## 🚀 Features

### Core Features (Free Version)
- **AI Content Generation**: Support for OpenAI GPT and Anthropic Claude APIs
- **RSS Feed Integration**: Fetch latest news from popular RSS feeds or custom feeds
- **Batch Post Creation**: Generate up to 5 unique blog posts per batch
- **Customizable Content**: Configure tone of voice, word count, and categories
- **Security First**: Encrypted API key storage and comprehensive input sanitization
- **WordPress Native**: Built with WordPress coding standards and native UI

### Pro Features (Coming Soon)
- **Automated Scheduling**: Set up automatic post generation with WP-Cron
- **Extended Batch Size**: Generate up to 30 posts per batch
- **Featured Image Generation**: AI-powered featured images for posts
- **SEO Optimization**: Automatic meta descriptions and SEO-friendly content
- **Advanced Analytics**: Track post performance and engagement

## 📋 Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- MySQL 5.6 or higher
- OpenAI API key or Anthropic API key
- cURL extension enabled

## 🔧 Installation

### Method 1: WordPress Admin (Recommended)
1. Download the plugin zip file from the [releases page](https://github.com/arunrajiah/ai-auto-news-poster/releases)
2. Log in to your WordPress admin dashboard
3. Navigate to **Plugins > Add New**
4. Click **Upload Plugin** and select the downloaded zip file
5. Click **Install Now** and then **Activate**

### Method 2: Manual Installation
1. Download and extract the plugin files
2. Upload the `ai-auto-news-poster` folder to `/wp-content/plugins/`
3. Activate the plugin through the WordPress admin **Plugins** menu

### Method 3: Git Clone (Developers)
```bash
git clone https://github.com/arunrajiah/ai-auto-news-poster.git
cd ai-auto-news-poster
# Copy to your WordPress plugins directory
cp -r . /path/to/wordpress/wp-content/plugins/ai-auto-news-poster/
```

## ⚙️ Configuration

1. After activation, navigate to **Settings > AI Auto News Poster**
2. Configure the following settings:
   - **LLM Provider**: Choose between OpenAI, Anthropic, or Custom API
   - **API Key**: Enter your API key (stored securely and encrypted)
   - **Categories**: Select WordPress categories for generated posts
   - **Word Count**: Choose short (300-500), medium (500-800), or long (800-1200) posts
   - **Tone of Voice**: Select Neutral, Professional, or Friendly tone

### Getting API Keys

#### OpenAI API Key
1. Visit [OpenAI Platform](https://platform.openai.com/)
2. Sign up or log in to your account
3. Navigate to **API Keys** section
4. Create a new API key
5. Copy and paste into the plugin settings

#### Anthropic API Key
1. Visit [Anthropic Console](https://console.anthropic.com/)
2. Sign up or log in to your account
3. Navigate to **API Keys** section
4. Create a new API key
5. Copy and paste into the plugin settings

## 🎯 Usage

### Generating Posts
1. Go to **Settings > AI Auto News Poster**
2. Ensure all settings are configured
3. Click **Generate 5 Posts** button
4. Wait for the process to complete (usually 30-60 seconds)
5. Check **Posts > All Posts** for new draft posts
6. Review, edit, and publish the generated content

### Managing Generated Content
- All generated posts are created as **drafts** for manual review
- Posts are automatically categorized based on your settings
- Each post includes a note indicating it was AI-generated
- You can edit, modify, or delete posts as needed before publishing

## 🔒 Security Features

- **Encrypted API Key Storage**: API keys are encrypted before storage
- **Input Sanitization**: All user inputs are sanitized and validated
- **Nonce Verification**: CSRF protection for all admin actions
- **Capability Checks**: Proper WordPress permission handling
- **SQL Injection Prevention**: Prepared statements for database queries
- **XSS Protection**: Output escaping for all displayed content

## 🏗️ Technical Architecture

### File Structure
```
ai-auto-news-poster/
├── ai-auto-news-poster.php      # Main plugin file
├── includes/
│   ├── class-admin-settings.php  # Admin settings management
│   ├── class-news-fetch.php      # RSS feed processing
│   ├── class-ai-generator.php    # AI content generation
│   └── class-post-creator.php    # WordPress post creation
├── admin/
│   └── settings-page.php         # Admin interface
├── assets/
│   ├── css/admin.css             # Admin styling
│   └── js/admin.js               # Admin JavaScript
├── readme.txt                    # WordPress repository readme
└── README.md                     # This file
```

### Key Classes
- **AI_Auto_News_Admin_Settings**: Handles plugin configuration and settings
- **AI_Auto_News_Fetch**: Manages RSS feed fetching and parsing
- **AI_Auto_News_AI_Generator**: Interfaces with AI APIs for content generation
- **AI_Auto_News_Post_Creator**: Creates and manages WordPress posts

## 🔌 API Integration

### Supported AI Providers

#### OpenAI Integration
- **Model**: GPT-3.5-turbo (default) or GPT-4
- **Endpoint**: `https://api.openai.com/v1/chat/completions`
- **Authentication**: Bearer token

#### Anthropic Integration
- **Model**: Claude-3-haiku (default) or Claude-3-sonnet
- **Endpoint**: `https://api.anthropic.com/v1/messages`
- **Authentication**: API key header

#### Custom API
- Configurable endpoint and authentication
- Must follow OpenAI-compatible response format

## 🐛 Troubleshooting

### Common Issues

**Posts not generating**
- Verify API key is correct and has sufficient credits
- Check WordPress error logs for detailed error messages
- Ensure RSS feeds are accessible and returning content

**API errors**
- Confirm API key has proper permissions
- Check API rate limits and usage quotas
- Verify internet connectivity and firewall settings

**Permission errors**
- Ensure user has `manage_options` capability
- Check WordPress file permissions
- Verify plugin activation was successful

### Debug Mode
Enable WordPress debug mode to see detailed error messages:
```php
// Add to wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

## 🤝 Contributing

We welcome contributions! Please follow these steps:

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/amazing-feature`
3. Commit your changes: `git commit -m 'Add amazing feature'`
4. Push to the branch: `git push origin feature/amazing-feature`
5. Open a Pull Request

### Development Setup
```bash
# Clone the repository
git clone https://github.com/arunrajiah/ai-auto-news-poster.git
cd ai-auto-news-poster

# Set up local WordPress development environment
# Copy plugin to WordPress plugins directory
cp -r . /path/to/wordpress/wp-content/plugins/ai-auto-news-poster/
```

### Coding Standards
- Follow [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/)
- Use proper sanitization and escaping
- Include comprehensive error handling
- Add inline documentation for complex functions

## 📝 Changelog

### Version 1.0.0 (2025-09-08)
- Initial release
- Core AI content generation functionality
- OpenAI and Anthropic API integration
- RSS feed processing
- Batch post creation (5 posts limit)
- Admin settings interface
- Security enhancements
- WordPress repository compatibility

## 🔮 Roadmap

### Version 1.1.0 (Planned)
- [ ] Featured image generation
- [ ] SEO meta optimization
- [ ] Content templates
- [ ] Multi-language support

### Version 2.0.0 (Pro Features)
- [ ] Automated scheduling
- [ ] Extended batch sizes
- [ ] Advanced analytics
- [ ] Premium support

## 📄 License

This project is licensed under the GPL v2 License - see the [LICENSE](LICENSE) file for details.

## 👨‍💻 Author

**Arun Rajiah**
- GitHub: [@arunrajiah](https://github.com/arunrajiah)
- Website: [Contact via GitHub](https://github.com/arunrajiah)

## 🙏 Acknowledgments

- WordPress community for excellent documentation
- OpenAI and Anthropic for powerful AI APIs
- RSS feed providers for news content
- Beta testers and early adopters

## 📞 Support

For support, please:
1. Check the [troubleshooting section](#-troubleshooting)
2. Search [existing issues](https://github.com/arunrajiah/ai-auto-news-poster/issues)
3. Create a [new issue](https://github.com/arunrajiah/ai-auto-news-poster/issues/new) if needed

---

**Made with ❤️ for the WordPress community**