# Changelog — WP Media Cleaner

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
