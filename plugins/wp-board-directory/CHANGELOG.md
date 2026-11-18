# Changelog — WP Board & Directory

## [2026-11__1.06] - 2026-11-18
### Fixed
- Fixed fatal stall / endless loading on Dashboard Ads tab (`https://doska-cz.ru/dashboard/?tab=ads`):
  - Removed deprecated unverified call to deleted taxonomy `acadp_categories`.
  - Added safe taxonomy lookup with `vb_category` first and strict `is_wp_error()` checks.
  - Page now loads instantly in < 0.2s.

### Added
- Permanent Attached Media Deletion on Item Removal:
  - Deleting an ad or company (both single action and bulk delete) permanently purges all attached images from disk (`_thumbnail_id`, `_vb_images`, legacy `images`, `_gallery_images`, `_listing_images`, `_atbdp_gallery`, `_atbdp_header_image`, and all attachments with `post_parent = $post_id`) via `wp_delete_attachment( $id, true )`.
- Turnkey Orphan Media Cleaner Engine (`VB_Media_Cleaner`):
  - Added dedicated Admin GUI tool under **Доска и Фирмы ➔ Очистка картинок** (`vb-media-cleaner`).
  - Scans entire WordPress media library, cross-references all active posts, pages, listings, companies, featured images, galleries, logos, and site icons.
  - Displays interactive audit dashboard: Total Media, Used on Site, Orphan/Unused garbage count, and freed MB space.
  - Provides 1-Click "Выбрать все неиспользуемые" and bulk permanent disk purge.
  - Added WP-CLI command `wp vb clean-media [--dry-run]`.

## [2026-11__1.05] - 2026-11-18
### Added
- Complete elimination and deletion of legacy `Directorist` and `Advanced Classifieds and Directory Pro (ACADP)` plugins.
- Modern Single Listing Template (`templates/single-listing.php`):
  - High-res photo gallery with thumbnail strip.
  - Price in Czech Koruna (Kč) and approx. EUR (€).
  - Quick contact bar: Click-to-Call (`tel:`), direct WhatsApp (`wa.me`), Telegram, and website links.
  - Safe deal recommendations and dual anti-fraud ratings (`👍 Рекомендую / 👎 Сомнительно`).
  - Related listings from the same category.
- Taxonomy Category Archive Template (`templates/taxonomy-category.php`):
  - Breadcrumb navigation, subcategory pills/chips, Czech city/region filter.
  - Responsive 3-column equal-height cards grid with 260px cover images.
- Turnkey Frontend Submission Engine (`templates/submit-form.php`):
  - Client-side WebP compression up to 15 photos (max 1024px) with zero server overhead.
  - Category and Czech region selectors.
  - Cloudflare Turnstile bot protection integration.
- Full Czech URL Slugs (`CZ Standard`):
  - Listings: `/inzerat/<slug>/` (archive: `/inzeraty/`)
  - Categories: `/kategorie/<slug>/`
  - Locations: `/mesto/<slug>/`
- Aliased legacy shortcodes (`acadp_categories`, `acadp_listings`, `acadp_listing_form`, `directorist_add_listing`) to native `wp-board-directory` handlers.

## [2026-11__1.04] - 2026-11-18
### Added
- Dashboard Bulk Actions (`/kabinet/?tab=ads` & `/kabinet/?tab=companies`):
  - Row checkboxes and "Select All" toggle for multi-selection.
  - Bulk action toolbar with direct "🗑️ Удалить выбранные" and renewal options: «⏳ Продлить на 1 месяц», «⏳ Продлить на 3 месяца», «⏳ Продлить на 6 месяцев», «⏳ Продлить на 1 год».
  - Automatic recalculation of expiration dates (`expiry_date`) and refreshing of publish timestamps.
- Single Row Actions:
  - Added explicit red «Удалить» button with JS confirmation dialog in the "Действия" column for each ad and company.
  - Expiration date badge (`до ДД.ММ.ГГГГ`) in the date column.
- Visual & Design Refinements:
  - Company Directory Cards (`/company/`): increased image height from 220px to 280px with full width (`padding: 0`) and `object-fit: cover`.
  - Homepage Classifieds Cards (`/`): increased image height by 30% (from 200px to 260px) and card minimum height to 490px, eliminating horizontal cropping.
  - Included expired companies in user dashboard so owners can easily renew them with 1 click.

## [2026-11__1.03] - 2026-11-18
### Added
- Company Directory Filter Bar: instant filtering by Category (from classifieds category tree), by Region/City (Czech kraje), and keyword search.
- Turnkey Unified User Dashboard (`[vb_dashboard]`):
  - Tab 1: «Мой профиль» — Edit Display Name, Phone, City/Region, Company Name, and Bio (no password change required).
  - Tab 2: «Мои объявления» — Table of user's classifieds with thumbnail, status, views, view/edit actions, and «+ Подать объявление» button.
  - Tab 3: «Мои компании» — Table of user's company cards with logo, address, phone, ratings, view/edit actions, and «+ Добавить компанию» button.
  - Header summary card with user avatar, company badge, contact details, and listing statistics.
- Aliased old `[acadp_user_dashboard]` and `[directorist_user_dashboard]` shortcodes to the unified dashboard.
- Linked menu items «Мой профайл», «Личный кабинет», «Мои компании», and «Управление объявлениями» to the unified dashboard.

## [2026-11__1.02] - 2026-11-18
### Added
- Left sidebar «Все страны»: removed top gap, converted to clean unbold links with full-width thin divider lines.
- Categories grid: increased typography size for parent and subcategories for improved readability on desktop and mobile.
- Company Directory cards: increased image height to 220px, eliminated empty tagline void, pinned title and fields directly beneath the image.
- Classifieds Homepage Grid: switched ads to 3-column equal-height cards (`view="grid"`, `columns="3"`) with 200px cover images, clamp titles, prominent prices and meta.

## [2026-11__1.01] - 2026-11-18
### Added
- Filter for ACADP categories template (`acadp_get_template` and `acadp_load_template`) with balanced 3 columns (`templates/acadp-categories-list.php`).
- Horizontal dividers between category rows and small horizontal accent line (`vb-cat-accent-line`) above each main category title.
- Reordered categories: swapped «Развлечения» and «Для дома» for visual balance across columns.
- Strict 3-column CSS Grid for Company Directory (`.vb-company-grid`) with uniform card height (`min-height: 400px`, fixed title & tagline clamps, guaranteed 3-field alignment).

## [2026-11__1.00] - 2026-11-17
### Added
- Initial release of unified Classified Ads & Business Directory engine.
- Custom Post Type `vb_listing` and taxonomies `vb_category`, `vb_location`.
- Anti-fraud ratings & reviews table `wp_vb_ratings` and fast search index `wp_vb_index`.
- 1-Click Migration Wizard for ACADP and Directorist with full author relation preservation.
- Shortcodes: `[vb_categories]`, `[vb_listings]`, `[vb_companies]`, `[vb_dashboard]`.
- Backward-compatible shortcode aliases: `[acadp_categories]`, `[acadp_listings]`.
- Client-side WebP image compressor up to 15 photos max 1024px.
