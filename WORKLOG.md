# 📋 WordPress Infrastructure & Plugin Engineering Worklog

> Repository: [GinCz/WordPress](https://github.com/GinCz/WordPress)  
> Host: `DE-222` (`152.53.182.222`)  
> Target Network: 11 International Classified & Business Directory Portals  
> Author: Vladimir Bulantsev (@GinCz) & AI  
> Date: 2026-11-23

---

## 🌐 Network of 11 Portals
1. `doska-de.ru` (Germany - Primary pilot node)
2. `doska-cz.ru` (Czech Republic)
3. `doska-esp.ru` (Spain)
4. `doska-fr.ru` (France)
5. `doska-gr.ru` (Greece)
6. `doska-hun.ru` (Hungary)
7. `doska-isl.ru` (Iceland)
8. `doska-it.ru` (Italy)
9. `doska-mld.ru` (Moldova)
10. `doska-pl.ru` (Poland)
11. `doska-ua.ru` (Ukraine)

---

## 🛠️ Summary of Implementations & Upgrades

### 0. `wp-media-cleaner` (v1.04) — Universal Media Cleaner & Auto-Purge Engine
- **v1.04 Flatsome UX Builder & Multi-Node Rollout**:
  - Enhanced inline content scanner with deep shortcode support (`bg="..."`, `img="..."`, `image="..."`, `id="..."`, `[ux_image]`, `[ux_banner]`, `[section]`).
  - Strict SQL parenthesis precedence and full coverage of non-standard post types (Flatsome `blocks`, `ux_template`, Gutenberg `wp_block`).
  - Synchronized `VB_MEDIA_CLEANER_VERSION` runtime constant.
  - Successfully deployed, verified, and activated on `detailing-alex.eu` (Flatsome 3.18.1).
  - Multi-node propagation completed across all 12 existing sites on `DE-222` (`ru-tv.eu` and 11 `doska-*` portals).
  - Audited orphan images via WP-CLI scan: detected 2 legacy orphan images (0.25 MB) on `detailing-alex.eu` with zero impact on live content.

### 1. `wp-login-branding` (v1.01 & v1.02) — Authentication & Login Visual Architecture
- **v1.02 Dual-Line Header Branding & Auto-Update Engine**:
  - Added two clean, non-wrapping single lines directly under the national flag/logo:
    - Line 1: Site Title (`get_bloginfo('name')`, e.g. "ДоСкА объявлений в Германии") in bold white 15px with drop shadow.
    - Line 2: Administration Subtitle («Вход в администрирование сайта») in soft violet 12.5px.
  - Kept total vertical footprint under 35px so login card remains fully within the viewport without vertical scroll.
  - Integrated `auto_update_plugin` filter into `VB_Login_Branding` and `vladimir-ai-updater.php` to enable seamless WordPress background auto-updates.
- **v1.01 Compact Viewport Polish**:
  - Reduced container, card, and input vertical margins/paddings so the entire authentication form (inputs, social buttons, Turnstile, submit button, single-line navigation, and copyright footer) fits cleanly within standard screen heights without vertical scrolling.
  - Eliminated header logo cropping on 768px/800px displays.
- **Removed Subtitle Badge**:
  - Eliminated redundant button under national logo containing country indicators (e.g. "DE Вход в личный кабинет"). Focus is kept purely on site brand identity.
- **Suppressed Bulky Logout Alert**:
  - Hidden `#login-message` / `.login .message` ("Вы вышли из системы") for seamless card presentation.
- **Single-Line Glass Navigation**:
  - Replaced stacked WordPress links with a sleek single horizontal row: `Регистрация • Забыли пароль? • ← На главную`.
- **Turnkey Multi-Node Deployment**:
  - Activated and configured on all 11 portals.

### 2. `wp-board-directory` (v1.31 & v1.32) — Core Directory & Listing Architecture
- **Hardened Media Deletion Purge Engine (`VB_Media_Cleaner`)**:
  - Hooked into `before_delete_post` for `vb_listing`, `acadp_listings`, and `at_biz_dir`.
  - Scans all image meta keys (`_vb_images`, `images`, `_gallery_images`, `_listing_images`, `_company_logo`, `logo`, `_thumbnail_id`) supporting arrays, comma-delimited strings, and JSON strings.
  - Recursively purges all child attachments (`post_parent = $post_id`).
  - Calls `wp_delete_attachment($id, true)`: unlinks original upload, all intermediate scaled crops (`-150x150`, `-300x200`, `-1024x768`), and WebP counterparts from disk.
  - Verified with automated test: 0 orphaned image files left behind upon ad or company deletion.
- **Automated Company Submission Flow**:
  - User cabinet «Мои компании» tab: "+ Добавить компанию" and "+ Зарегистрировать компанию" buttons point to `/add-listing/?type=company`.
  - In `templates/submit-form.php`: automatically pre-selects and activates the «🏢 Компания / Каталог фирм» publication type card whenever `type=company` parameter is present.
  - Dynamically updates page header to `🏢 Добавить компанию в каталог` and title input placeholder to business naming format.
- **Localized Listing Page Title**:
  - Filtered `the_title` and updated database `post_title` on page `add-listing` (ID 354) from raw English `Add Listing` to `Добавить новое объявление или компанию` across all 11 portals.
- **Location Dropdown Typographic Sanitation (v1.32)**:
  - Eliminated broken Unicode regional indicator flag codes (`DE`, `EU`, `RU`, `UA`) from `<optgroup>` labels in `VB_CPT::render_location_options()`. Windows font `Segoe UI Emoji` does not render flag glyphs in native HTML dropdowns, falling back to ugly dangling Latin letters.
  - Optgroups now render with 100% clean typography: `Германия`, `Европа`, `Россия`, `Украина`.
- **Interactive Quick-Select Location Buttons («Кнопочки»)**:
  - Added dedicated quick-select pills (`.vb-quick-locations`) directly beneath the location dropdown in `templates/submit-form.php`: `[📍 Столица]`, `[Вся страна]`, `[🌐 OnLine]`, `[Европа]`, `[Россия]`, `[Украина]`.
  - Implemented real-time two-way synchronization: clicking any quick pill instantly selects the corresponding option in the native `<select>`, and choosing any item in the dropdown highlights the matching pill.

### 3. VKontakte Social Login Deactivation
- Deactivated VKontakte OAuth across all 11 portals due to upstream VK OAuth security/redirect restrictions (`Security Error`).
- Cleaned `mo_openid_apps_list` to retain strictly Google OAuth (`Login with Google`).
- Set `mo_openid_vkontakte_enable` and `mo_openid_enable_custom_app_vkontakte` to `0`.
- Purged all stale/orphan VK linkages from `wp_mo_openid_linked_user`.

### 4. `wp-board-directory` (v1.37) — Multi-Phone Contact Buttons, Author Archive & Clickable Category Links
- **Multi-Phone Parsing & Dynamic Contact Actions**:
  - Implemented regex parser (`preg_split('/[,;\/|\n]+/', ...)`): safely parses multiple phone numbers from `_vb_phone` (and user profile meta fallbacks), sanitizing each number into valid E.164-compatible dial string.
  - Generates dedicated individual click-to-call buttons (`tel:<digits>`) for each phone number.
  - Generates dedicated WhatsApp click-to-chat buttons (`https://wa.me/<digits>`) for each valid phone number when dedicated `_vb_whatsapp` field is empty.
- **Author All Ads Button & Profile Links**:
  - Added «📋 Посмотреть все объявления (N)» button inside `.vb-contact-actions` linking directly to the author's public profile/ad archive.
  - Made author avatar and author display name clickable links pointing to the author archive.
- **Dedicated Author Archive Template (`templates/author.php`)**:
  - Routed `is_author()` via `route_templates` in `includes/class-shortcodes.php` to `templates/author.php`.
  - Renders a clean author profile card with large avatar (80px), author display name, pill badges (total published ads count, registration date, city), and bio description.
  - Renders a responsive grid of published `vb_listing` cards with pagination (`paginate_links`).
  - Added empty state handler when author has no published listings.
- **Clickable Taxonomy Links in Sidebar**:
  - Converted static category text in `.vb-sidebar-info-row` to a clickable link pointing to `get_term_link($primary_cat)`.
  - Converted static location text to a clickable link pointing to `get_term_link($primary_loc)`.
- **Author Filtering in Shortcodes & Search**:
  - Added `author` parameter support to `render_listings` (`[vb_listings]`) and `render_search` (`[vb_search]`), enabling filtered query by `author` parameter.
- **Packaging, Manifest & Multi-Node Rollout**:
  - Rebuilt package `plugins/zips/WP_Board-Directory_(VladiMIR+AI)_1.37.zip` and `plugins/zips/wp-board-directory.zip`.
  - Updated manifest in `GinCz/WordPress` and `GinCz/plugins` `updates.json` to version `2026-11__1.37`.
  - Manually deployed and verified live on `doska-cz.ru` (`https://doska-cz.ru/ad/proverennyy-tarolog-onlayn-rasklad-na-mysli-i-chuvstva-muzhchiny/` and `https://doska-cz.ru/author/gadanienatalia55/`).
  - Propagated to all 10 remaining portals on `DE-222` (`doska-de.ru`, `doska-esp.ru`, `doska-fr.ru`, `doska-gr.ru`, `doska-hun.ru`, `doska-isl.ru`, `doska-it.ru`, `doska-mld.ru`, `doska-pl.ru`, `doska-ua.ru`) with zero hanging tasks and cache flushed.

### 5. `wp-board-directory` (v1.38) — Differentiated Author vs Administrator Role Badges
- **Context Separation for Ownership vs Moderation Privileges**:
  - Identified logic defect where `current_user_can('edit_post')` triggered `👤 Ваша публикация` when an administrator viewed another client's listing.
  - Implemented explicit role segregation: `$is_real_author = ($author_id === $current_uid)` vs `$is_admin_mode = (!$is_real_author && current_user_can('edit_post'))`.
  - Top management bar: real author sees `👤 Ваша публикация`, while administrator/moderator sees `🛡️ Управление (Администратор)` in indigo `.vb-admin-badge`.
  - Sidebar block: real author sees `⚙️ Управление объявлением:`, while administrator sees `🛡️ Режим администратора:`.
  - Retained turnkey full edit/delete privileges for administrators without falsely claiming authorship of third-party ads.
- **Packaging, Manifest & Multi-Node Rollout**:
  - Rebuilt package `plugins/zips/WP_Board-Directory_(VladiMIR+AI)_1.38.zip` and `plugins/zips/wp-board-directory.zip`.
  - Updated manifest in `GinCz/WordPress` and `GinCz/plugins` `updates.json` to version `2026-11__1.38`.
  - Deployed to `doska-cz.ru` and propagated to all 10 remaining portals on `DE-222`.

---

## 📦 Deployment Packages
- `plugins/zips/WP_Board-Directory_(VladiMIR+AI)_1.38.zip`
- `plugins/zips/WP_Board-Directory_(VladiMIR+AI)_1.37.zip`
- `plugins/zips/wp-board-directory.zip`
- `plugins/zips/WP_Login-Branding_(VladiMIR+AI)_1.01.zip`
- `plugins/zips/WP_Login-Branding_(VladiMIR+AI)_1.00.zip`
- `plugins/zips/wp-login-branding.zip`
