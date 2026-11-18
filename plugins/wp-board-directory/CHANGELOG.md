# Changelog — WP Board & Directory

## [2026-11__1.11] - 2026-11-18
### Fixed
- Fixed font clipping in Category Filter Toolbar (`templates/taxonomy-category.php`):
  - Completely neutralized Flatsome theme's aggressive `select`/`input` overrides (`margin-bottom: 1em; padding-top: 1.1em; height: 3.0084em;`).
  - Implemented crisp `height: 40px`, centered `line-height: 40px`, `margin: 0 !important`, `padding: 0 4px !important`, and custom SVG chevron arrow, ensuring zero bottom-line font truncation across all browsers.
- Fully restored and re-linked all company logos and preview images:
  - Reinstated company image attachments (`post_parent` linked to `vb_listing`) from clean backup archives.
  - Linked featured thumbnails for all migrated companies.

## [2026-11__1.10] - 2026-11-18
### Added
- Complete Czech Republic Regions:
  - Integrated all 14 administrative regions (kraje) of the Czech Republic under parent taxonomy `Чехия` (`vb_location`): Prague, Central Bohemian, South Moravian (Brno), South Bohemian (České Budějovice), Plzeň, Karlovy Vary, Ústí nad Labem, Liberec, Hradec Králové, Pardubice, Vysočina (Jihlava), Olomouc, Moravian-Silesian (Ostrava), Zlín.
  - Added anti-spam trap regions (`Россия` / `Москва`, `Украина` / `Киев`) allowing spammers to self-segment for immediate 1-click administrative purging.
  - Dynamic hierarchical dropdowns with optgroups in frontend submission form (`templates/submit-form.php`) and category archive filter toolbar (`templates/taxonomy-category.php`).
- Clean Admin Filter URLs:
  - Added client-side form sanitizer to `#posts-filter` that disables empty parameters and default `-1`/`0` values before submission, completely eliminating URL clutter (such as `filter_action=Фильтр` and empty queries).

### Fixed
- Fixed `/company/` catalog query to strictly query `vb_listing`, eliminating duplicate cards caused by residual legacy Directorist posts.
- Restored missing published companies from trash and permanently purged orphaned legacy `at_biz_dir` posts.

## [2026-11__1.09] - 2026-11-18
### Fixed
- Fixed 502 Bad Gateway ("upstream sent too big header") on bulk admin actions with high post selection counts:
  - Tuned FastCGI buffers (`fastcgi_buffer_size 128k; fastcgi_buffers 8 128k;`).
  - Cleaned duplicate `DISALLOW_FILE_EDIT` warning in `wp-config.php`.

### Added
- Deep Admin Search:
  - Searching by email (`s=example@gmail.com`), phone number, or address in `edit.php?post_type=vb_listing` now automatically searches through custom meta fields (`_vb_email`, `_vb_phone`, `_vb_address`) and author profile accounts.

## [2026-11__1.08] - 2026-11-18
### Added
- Admin List Table Enhancements (`edit.php?post_type=vb_listing`):
  - Thumbnail / photo preview column (`Фото`).
  - Sortable `Телефон` and `Email` columns with direct click-to-call / mailto links.
  - Type badge column (`📢 Объявление` vs `🏢 Фирма`).
  - Price and Expiry status columns.
  - Dropdown filter bar for Rubrics (`vb_category`), Cities/Regions (`vb_location`), and Types.
- Frontend Category Filter Toolbar (`templates/taxonomy-category.php`):
  - Interactive city/region filter (`vb_location`), keyword search, and sorting (price / date).
- Anti-Spam Limitation:
  - Enforced hard limit: maximum 10 active listings per account/email in one category.

## [2026-11__1.07] - 2026-11-18
### Added
- Integration with standalone `WP Media Cleaner (VladiMIR+AI✅)`:
  - If `wp-media-cleaner` is active, the menu item «Доска и Фирмы» ➔ «Очистка картинок» seamlessly bridges to the full universal cleaner with WooCommerce support.
  - Retained native fallback cleaner and automatic media purge on ad/company deletion.

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
