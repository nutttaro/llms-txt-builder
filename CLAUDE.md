# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What This Plugin Does

NT LLMs.txt Builder is a WordPress plugin that generates `/llms.txt` and `/llms-full.txt` endpoints following the [llms.txt specification](https://llmstxt.org). The output is spec-compliant markdown listing all published content URLs with titles. The full variant adds post excerpts. It supports WooCommerce products/categories, custom post types, and custom taxonomies.

## Development Environment

This is a pure PHP WordPress plugin with no JS build step. It runs on XAMPP (PHP 8.3) with WordPress at `http://localhost`. The admin settings page is at **Settings > LLMs.txt Builder**.

**Testing:**
```bash
composer install                # Install PHPUnit + WP test polyfills
composer test                   # Run tests (requires WP test library)
WP_TESTS_DIR=/path/to/wp-tests-lib composer test  # Custom test lib path
```

The WP test library can be installed via `bin/install-wp-tests.sh` (standard WordPress plugin scaffold script). Tests live in `tests/`.

## Architecture

All classes live under the `NT\LLMSTXT` namespace. The singleton `LLMs_TXT_Generator` bootstraps everything on `plugins_loaded`.

**Request flow for `/llms.txt` and `/llms-full.txt`:**
`init` hook registers rewrite rules (`^llms\.txt$`, `^llms-full\.txt$`) -> WordPress matches route -> `template_redirect` fires `handle_llms_txt_request()` -> reads `ntllms_txt` query var -> delegates to `LLMs_TXT_Generator_Content::get_llms_txt_content()` or `get_llms_full_txt_content()` (each checks its own transient cache) -> outputs plain text and exits.

**Key classes:**

| File | Class | Role |
|------|-------|------|
| `nt-llms-txt-builder.php` | — | Entry point, defines constants, loads `LLMs_TXT_Generator` |
| `includes/class-llms-txt-generator.php` | `LLMs_TXT_Generator` | Singleton orchestrator, rewrite rules, request routing, auto-cache-invalidation hooks |
| `includes/class-llms-txt-generator-content.php` | `LLMs_TXT_Generator_Content` | Builds llms.txt/llms-full.txt output following the spec (markdown with titles/links/excerpts) |
| `includes/class-llms-txt-admin.php` | `LLMs_TXT_Admin` | Settings page, AJAX handlers for generate/clear-cache, admin script enqueue |
| `includes/class-llms-txt-cache.php` | `LLMs_TXT_Cache` | Wrapper around WordPress transients for both standard and full cache keys |
| `includes/class-llms-txt-meta-box.php` | `LLMs_TXT_Meta_Box` | Per-post meta box: "ignore this post" and "clear cache on update" checkboxes |

**Caching:** Two transients — `ntllms_txt_builder_cache_data` (standard) and `ntllms_txt_builder_full_cache_data` (full) — each with 24-hour TTL. Both are cleared automatically when:
- A tracked post type is published, trashed, or changes status (`transition_post_status`)
- A tracked taxonomy term is created, edited, or deleted (`created_term`, `edited_term`, `delete_term`)
- Manual clear via AJAX button or per-post meta box checkbox

**Per-post exclusion:** Posts can be excluded via `_ntllms_txt_builder_ignore_page` post meta.

**Settings storage:** Single `ntllms_txt_builder_options` option (array with keys: `post_types`, `taxonomies`, `include_archives`, `include_author_pages`, `overview_text`).

## Code Conventions

- **Text domain**: `nt-llms-txt-builder`
- **Function prefix**: `ntllms_txt_builder_` for global functions
- **Constant prefix**: `NT_LLMS_TXT_BUILDER_`
- **Option/transient prefix**: `ntllms_txt_builder_`
- **AJAX actions**: `ntllms_txt_builder_generate_file`, `ntllms_txt_builder_clear_cache_data`
- **Post meta keys**: `_ntllms_txt_builder_clear_cache`, `_ntllms_txt_builder_ignore_page`
- Admin JS uses jQuery (no build step), localized via `nt_llms_txt_builder` object

## SVN Publishing

The `svn/` directory contains the WordPress.org plugin SVN checkout (`svn/nt-llms-txt-builder/`) with trunk, tags, and assets. This is used for publishing to the WordPress plugin directory, not for development.
