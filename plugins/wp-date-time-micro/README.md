# WP Date & Time Micro (VladiMIR+AI✅)

**Version:** `2026-11__1.00`  
**Author:** Vladimir Bulantsev (`@GinCz`) & AI  
**License:** GPL-2.0-or-later  

---

## 📌 Description
Ultra-lightweight WordPress micro-plugin that replaces bulky third-party date/time shortcode plugins (such as `WP Date and Time Shortcode`).
Designed specifically for high-speed sites, Flatsome theme headers, footers, and content blocks.

## 🚀 Key Advantages
- **Zero Overhead:** No heavy classes, no background queries, no database bloat.
- **100% Timezone-Aware:** Uses native `wp_date()` respecting WordPress site timezone (`timezone_string` / `gmt_offset`).
- **Complete Drop-in Compatibility:** Provides 100% backward compatibility with `[wpdts-date]`, `[wpdts-time]`, `[wp-dt]`, etc.

## ⚙️ Shortcodes & Examples

| Shortcode | Default Output | Description |
|---|---|---|
| `[wpdts-date]` | `17 / 11 / 2026` | Current date according to WP settings |
| `[wpdts-time]` | `20:49` | Current time according to WP settings |
| `[wpdts-year]` | `2026` | 4-digit current year |
| `[wpdts-month]` | `11` | Current month (01-12) |
| `[wpdts-day]` | `17` | Current day of month (01-31) |
| `[wpdts format="d.m.Y H:i"]` | `17.11.2026 20:49` | Custom format |
| `[wpdts-date offset="+1 day"]` | Tomorrow's date | Relative time offset |
