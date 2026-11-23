# WP Media Cleaner (VladiMIR+AI✅)

**Universal high-performance media cleaner and auto-purge engine for WordPress & WooCommerce.**

Author: **VladiMIR (GinCz) + AI**  
Version: **2026-11__1.04**  
License: **GPL-2.0-or-later**  
Repository: [GinCz/WordPress](https://github.com/GinCz/WordPress/tree/main/plugins/wp-media-cleaner)

---

## 🚀 Key Features

1. **Full WooCommerce & WordPress Compatibility:**
   - Detects and protects Featured Images (`_thumbnail_id`).
   - Protects WooCommerce Product Galleries (`_product_image_gallery`).
   - Protects WooCommerce Product Variations and category thumbnails (`thumbnail_id`).
   - Protects Custom Post Type galleries (`_vb_images`, `images`, `_gallery_images`, etc.).
   - Scans and preserves inline content images (Gutenberg blocks, Flatsome `[ux_image]`, etc.).
   - Preserves theme branding (custom logo, site icon, header image).

2. **Smart Auto-Purge on Deletion:**
   - Automatically unlinks and deletes media files when a post, page, WooCommerce product, or custom listing is permanently removed.
   - Built-in cross-check prevents accidental deletion if an image is shared across multiple products or posts.

3. **Interactive Admin Dashboard:**
   - Accessible under **«Медиафайлы» ➔ «Очистка картинок»** (`upload.php?page=wp-media-cleaner`) and **«Инструменты»**.
   - Live metrics: Total media files, Active in use, Orphan garbage count, and freed MB space.
   - Interactive preview table with Select-All toggle and 1-Click permanent purge.

4. **WP-CLI Headless Support:**
   - `wp media-cleaner scan`: audit media library from terminal.
   - `wp media-cleaner clean [--dry-run] [--yes]`: automated maintenance.
