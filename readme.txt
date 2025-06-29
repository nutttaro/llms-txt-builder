=== LLMs.txt Builder ===
Contributors: nutttaro
Donate link: https://buymeacoffee.com/nutttaro
Tags: llms, ai, seo, sitemap, content-discovery
Requires at least: 6.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Generate an LLMs.txt file that helps AI models and large language models understand your WordPress website structure and content.

== Description ==

**LLMs.txt Builder** creates an LLMs.txt file (similar to robots.txt) that helps AI models and large language models (LLMs) understand your website structure and content. The file is accessible at `https://yourdomain.com/llms.txt` and includes links to all your published content.

= Key Features =

* **Post and Page Support**: Automatically includes page, post, categories, and tags
* **WooCommerce Support**: Automatically includes product pages, categories, and tags
* **Custom Post Types**: Configurable support for any custom post types
* **Custom Taxonomies**: Includes all public taxonomies and their terms
* **Admin Settings Page**: Easy configuration through WordPress admin
* **Caching System**: LLMs.txt content is cached for performance
* **Cache Management**: Manual cache clearing and automatic clearing on content updates
* **Meta Box Integration**: Option to clear cache when individual posts are updated

= What is LLMs.txt? =

LLMs.txt is a file format designed to help AI models and large language models understand how to interact with your codebase more effectively. It's similar in concept to `robots.txt` for web crawlers, but specifically for AI assistants.

The file provides context and instructions to AI models about your project, helping them:
* Understand your project structure and conventions
* Know which files are important vs. generated/ignored
* Follow your coding standards and patterns
* Understand the purpose and architecture of your codebase
* Provide more relevant and contextual assistance

= Example LLMs.txt Output =

```
# My WordPress Site
# Generated on: 2024-01-15 10:30:00
# URL: https://example.com

This is a WordPress website with the following content structure:

# Pages
https://example.com
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

# End of LLMs.txt
```

= WooCommerce Integration =

When WooCommerce is active, the plugin automatically includes:
* **Product Pages**: All published products
* **Product Categories**: All product category pages
* **Product Tags**: All product tag pages
* **Shop Page**: The main WooCommerce shop page

= Custom Post Types and Taxonomies =

The plugin automatically detects and allows configuration for:
* All public post types (excluding attachments)
* All public taxonomies
* Custom post types and taxonomies from other plugins

= Caching =

The LLMs.txt content is cached for 24 hours by default. This improves performance and reduces server load. The cache is automatically cleared when:
* Plugin settings are updated
* A post with cache clearing enabled is updated
* Manual cache clearing is triggered

== Installation ==

1. Upload the `llms-txt-builder` folder to your `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Settings > LLMs.txt to configure the plugin

== Frequently Asked Questions ==

= What is LLMs.txt? =

LLMs.txt is a file format designed to help AI models and large language models understand your website structure and content. It's similar to robots.txt but specifically for AI assistants.

= Where can I find my LLMs.txt file? =

Once activated, your LLMs.txt file will be available at `https://yourdomain.com/llms.txt`

= Does this plugin work with WooCommerce? =

Yes! The plugin automatically detects WooCommerce and includes all products, product categories, and product tags in the LLMs.txt file.

= Can I customize which content is included? =

Yes! Go to Settings > LLMs.txt to configure which post types, taxonomies, pages, and archives are included in your LLMs.txt file.

= How often is the LLMs.txt file updated? =

The content is cached for 24 hours by default. You can manually regenerate it anytime from the settings page, or it will automatically update when you modify the plugin settings.

= Does this affect my website's performance? =

No, the plugin uses efficient caching and only generates content when needed. The LLMs.txt file is served as a static text file.

= Can I clear the cache manually? =

Yes! There's a "Clear Cache" button in the plugin settings page, and you can also set individual posts to clear the cache when they're updated.

== Screenshots ==

1. Plugin settings page showing configuration options
2. Meta box on post edit screen for cache management
3. Example LLMs.txt output in browser

== Changelog ==

= 1.0.0 =
* Initial release
* Basic LLMs.txt generation
* WooCommerce support
* Admin settings page
* Caching system
* Meta box integration
* Custom post types and taxonomies support
* AJAX-powered generation and cache clearing

== Upgrade Notice ==

= 1.0.0 =
Initial release of LLMs.txt Builder plugin.

== Requirements ==

* WordPress 6.0 or higher
* PHP 7.4 or higher
* WooCommerce 3.0 or higher (for WooCommerce features)

== Support ==

For support, feature requests, or bug reports, please contact the developer.

== Credits ==

Developed for the WordPress community to help AI models better understand website content and structure.

== License ==

This plugin is licensed under the GPL v2 or later. 