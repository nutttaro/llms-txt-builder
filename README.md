# LLMs.txt Builder

A WordPress plugin that generates an LLMs.txt file containing all links from your website, with support for WooCommerce, custom post types, and custom taxonomies.

## Description

This plugin creates an LLMs.txt file (similar to robots.txt) that helps AI models and large language models (LLMs) understand your website structure and content. The file is accessible at `https://yourdomain.com/llms.txt` and includes links to all your published content.

## Features

- **Post and Page Support**: Automatically includes page, post, categories, and tags
- **WooCommerce Support**: Automatically includes product pages, categories, and tags
- **Custom Post Types**: Configurable support for any custom post types
- **Custom Taxonomies**: Includes all public taxonomies and their terms
- **Admin Settings Page**: Easy configuration through WordPress admin
- **Caching System**: LLMs.txt content is cached for performance
- **Cache Management**: Manual cache clearing and automatic clearing on content updates
- **Meta Box Integration**: Option to clear cache when individual posts are updated

## Installation

1. Upload the `nt-llms-txt-builder` folder to your `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Settings > LLMs.txt Builder to configure the plugin

## Configuration

### General Settings

The plugin settings page allows you to:

- **Enable/Disable Post Types**: Choose which post types to include in LLMs.txt
- **Enable/Disable Taxonomies**: Select which taxonomies to include
- **Include Archives**: Include archive pages (blog, shop, etc.)
- **Include Author Pages**: Add author archive pages

### Cache Management

- **Manual Generation**: Click "Generate LLMs.txt" to manually create the file
- **Clear Cache**: Remove cached content to force regeneration
- **Automatic Clearing**: Cache is automatically cleared when settings are updated

### Post-Level Control

Each post, page, or custom post type includes a meta box that allows you to:
- Clear the LLMs.txt cache when that specific post is updated

## Usage

### Accessing LLMs.txt

Once activated, two endpoints are available:
```
https://yourdomain.com/llms.txt        # Standard: titles and links
https://yourdomain.com/llms-full.txt   # Full: titles, links, and excerpts
```

### Example LLMs.txt Output

The output follows the [llms.txt specification](https://llmstxt.org) format:

```
# My WordPress Site

> A brief description of the site

## Pages

- [About Us](https://example.com/about/)
- [Contact](https://example.com/contact/)

## Posts

- [My First Post](https://example.com/2024/01/15/my-first-post/)
- [Another Post](https://example.com/2024/01/14/another-post/)

## Categories

- [Technology](https://example.com/category/technology/)
- [Business](https://example.com/category/business/)

## Products

- [Sample Product](https://example.com/product/sample-product/)
```

The `llms-full.txt` variant includes excerpts after each link:

```
- [My First Post](https://example.com/2024/01/15/my-first-post/): An introduction to our blog covering...
```

## WooCommerce Integration

When WooCommerce is active, the plugin automatically includes:

- **Product Pages**: All published products
- **Product Categories**: All product category pages
- **Product Tags**: All product tag pages
- **Shop Page**: The main WooCommerce shop page

## Custom Post Types and Taxonomies

The plugin automatically detects and allows configuration for:

- All public post types (excluding attachments)
- All public taxonomies
- Custom post types and taxonomies from other plugins

## Caching

The LLMs.txt content is cached for 24 hours by default. This improves performance and reduces server load. The cache is automatically cleared when:

- Any tracked post type is published, unpublished, or trashed
- Any tracked taxonomy term is created, edited, or deleted
- Plugin settings are updated
- A post with cache clearing enabled is updated
- Manual cache clearing is triggered

## Requirements

- WordPress 6.0 or higher
- PHP 7.4 or higher
- WooCommerce 3.0 or higher (for WooCommerce features)

## Changelog

### Version 1.1.0
- New: llms.txt spec-compliant output with markdown titles and links
- New: `/llms-full.txt` endpoint with post excerpts
- New: Rewrite rules replace `REQUEST_URI` check for better performance
- New: Auto-invalidate cache when tracked posts or terms change
- New: Redesigned settings page with card layout, live preview panel, and cache status indicator
- New: Copy-to-clipboard buttons for endpoint URLs
- New: Select All / None toggles for post type and taxonomy checkboxes
- New: Permalink flush detection with guidance notice on fresh installs
- New: PHPUnit test infrastructure with content generation and cache invalidation tests
- Fix: Cache duration documented as 1 hour but was 24 hours — README corrected
- Fix: Regenerate button showing `[object Object]` instead of success message
- Fix: Live preview not loading due to cached old JavaScript
- Fix: Endpoint action button icons not vertically centered
- Fix: Content Selection checkboxes styled as clean bordered list with item counts
- Fix: Asset versioning uses `filemtime` for automatic cache busting
- Fix: Tested up to bumped to WordPress 6.9
- Fix: Plugin check issues — translators comments, test bootstrap prefixing, `.distignore`

### Version 1.0.0
- Initial release
- Basic LLMs.txt generation
- WooCommerce support
- Admin settings page
- Caching system
- Meta box integration

## Support

For support, feature requests, or bug reports, please visit the plugin's contact the developer.

## License

This plugin is licensed under the GPL v2 or later.

## Credits

Developed for WordPress and WooCommerce communities to help AI models better understand website content and structure.
