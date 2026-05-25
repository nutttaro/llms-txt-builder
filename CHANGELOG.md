# Changelog

All notable changes to the LLMs.txt Builder plugin will be documented in this file.

## [1.2.0] - 2026-05-25

### Added
- WordPress 7.0 compatibility — tested up to 7.0
- Block editor sidebar panel (`PluginDocumentSettingPanel`) replaces classic meta box in Gutenberg, preserving collaboration mode
- Post meta registered with REST API (`show_in_rest`) for block editor support
- Classic meta box kept as fallback for the classic editor via `__back_compat_meta_box`
- WordPress 7.0 Abilities API integration — registers llms.txt content as a discoverable ability for AI agents (backward-compatible with WP < 7.0)

### Fixed
- Cache action button icons not vertically aligned with text
- Uninstall now cleans up per-post meta from `wp_postmeta`

## [1.1.0] - 2026-05-18

### Added
- llms.txt spec-compliant output with markdown titles and links
- `/llms-full.txt` endpoint with post excerpts
- Rewrite rules replace `REQUEST_URI` check for better performance
- Auto-invalidate cache when tracked posts or terms change
- Redesigned settings page with card layout, live preview panel, and cache status indicator
- Copy-to-clipboard buttons for endpoint URLs
- Select All / None toggles for post type and taxonomy checkboxes
- Permalink flush detection with guidance notice on fresh installs
- PHPUnit test infrastructure with content generation and cache invalidation tests

### Fixed
- Cache duration documented as 1 hour but was 24 hours — README corrected
- Regenerate button showing `[object Object]` instead of success message
- Live preview not loading due to cached old JavaScript
- Endpoint action button icons not vertically centered
- Content Selection checkboxes styled as clean bordered list with item counts
- Asset versioning uses `filemtime` for automatic cache busting
- Tested up to bumped to WordPress 6.9
- Plugin check issues — translators comments, test bootstrap prefixing, `.distignore`

## [1.0.0] - 2025-07-15

### Added
- Initial release
- Basic LLMs.txt generation
- WooCommerce support
- Admin settings page
- Caching system
- Meta box integration
- Custom post types and taxonomies support
- AJAX-powered generation and cache clearing
