# Changelog — WP Media Cleaner

## [2026-11__1.04] - 2026-11-23
### Enhanced
- **Flatsome Theme & UX Builder Deep Integration**: Added universal shortcode attribute scanning (`bg="123"`, `img="123"`, `image="123"`, `id="123"`) protecting Flatsome banners, sections, UX image grids, and team elements.
- **Universal Post Type Content Scanning**: Expanded inline content scan across all active post types (including Flatsome `blocks`, `ux_template`, Gutenberg `wp_block`) with strict SQL operator precedence.
- **Version Harmonization**: Synchronized runtime `VB_MEDIA_CLEANER_VERSION` constant with plugin metadata to `2026-11__1.04`.

## [2026-11__1.03] - 2026-11-23
### Fixed
- **Deep JSON Gallery & Company Logo Immunity**: Added native JSON array parsing (`json_decode`) for `_vb_images` and included `_company_logo` in custom gallery detection.
- **Strict Safe Deletion Guards**: `is_attachment_safe_to_delete()` now checks `_thumbnail_id`, `_company_logo`, `_vb_images` (JSON & serialized), and invokes `is_protected_media()` before allowing any attachment removal.
- **Zero-Loss Auto-Purge**: Eliminated accidental deletion of reused media during post migrations or deletions.

## [2026-11__1.00] - 2026-11-18
### Added
- Initial standalone release of `WP Media Cleaner (VladiMIR+AI✅)`.
- Full dual support for standard WordPress and WooCommerce:
  - Protection and scanning for WooCommerce product galleries (`_product_image_gallery`).
  - Protection for variation images and category thumbnails (`thumbnail_id`).
  - Protection for post thumbnails, inline images (`Gutenberg`, `Flatsome`, `Elementor`), and theme assets.
- Built-in Auto-Purge engine (`before_delete_post`): automatically deletes attached media when posts, pages, or WooCommerce products are permanently removed.
- Admin GUI Dashboard under **Медиафайлы ➔ Очистка картинок** (`upload.php?page=wp-media-cleaner`) and **Инструменты**.
- WP-CLI commands: `wp media-cleaner scan` and `wp media-cleaner clean [--dry-run] [--yes]`.
- Shared auto-update client integration (`vladimir-ai-updater.php`) and multi-language support (`vladimir-ai-i18n.php`).
