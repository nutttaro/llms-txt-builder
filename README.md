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

1. Upload the `llms-txt-builder` folder to your `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Settings > LLMs.txt to configure the plugin

## Configuration

### General Settings

The plugin settings page allows you to:

- **Enable/Disable Post Types**: Choose which post types to include in LLMs.txt
- **Enable/Disable Taxonomies**: Select which taxonomies to include
- **Include Pages**: Add static pages to the LLMs.txt file
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

Once activated, your LLMs.txt file will be available at:
```
https://yourdomain.com/llms.txt
```

### Example LLMs.txt Output

```
# LLMs.txt for My WordPress Site
# Generated on: 2024-01-15 10:30:00
# URL: https://example.com

# Project Overview
This is a WordPress website with the following content structure:

# Homepage
https://example.com

# Pages
https://example.com/about/
https://example.com/contact/
https://example.com/privacy-policy/

# Posts
https://example.com/2024/01/15/my-first-post/
https://example.com/2024/01/14/another-post/

# Categories
https://example.com/category/technology/
https://example.com/category/business/

# Tags
https://example.com/tag/wordpress/
https://example.com/tag/development/

# WooCommerce
## Product Categories
https://example.com/product-category/electronics/
https://example.com/product-category/clothing/

## Products
https://example.com/product/sample-product/
https://example.com/product/another-product/

# End of LLMs.txt
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

The LLMs.txt content is cached for 1 hour by default. This improves performance and reduces server load. The cache is automatically cleared when:

- Plugin settings are updated
- A post with cache clearing enabled is updated
- Manual cache clearing is triggered

## Requirements

- WordPress 6.0 or higher
- PHP 7.4 or higher
- WooCommerce 3.0 or higher (for WooCommerce features)

## Changelog

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