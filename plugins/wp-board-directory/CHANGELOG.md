# Changelog — WP Board & Directory

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
