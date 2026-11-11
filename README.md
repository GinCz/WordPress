# 🌐 WordPress Ecosystem & Ultra-Light Plugins Suite (VladiMIR+AI)

> **Repository:** [GinCz/WordPress ↗](https://github.com/GinCz/WordPress)  
> **Author & Architect:** Ing. Vladimir Bulantsev ([GinCz ↗](https://github.com/GinCz))  
> **Brand Tag:** `(VladiMIR+AI✅)`  
> **Production Nodes:** DE-222 (`152.53.182.222`), RU-109 (`212.109.223.109`), ORACLE-49 (`92.5.111.49`)  

---

## 📌 Repository Overview

Public repository of architectural solutions, high-performance server configurations, and a proprietary suite of ultra-lightweight **WordPress** and **WooCommerce** micro-plugins developed by **Vladimir Bulantsev (GinCz)** in collaboration with AI.

All plugins are delivered **100% open-source**, completely free of third-party advertisements, tracking pixels, obfuscated external scripts, or bloated database queries — significantly outperforming heavy legacy alternatives on WordPress.org.

---

## ⚡ Suite of Proprietary Micro-Plugins (`plugins/`)

All plugins and full source trees are located in the [`plugins/` ↗](https://github.com/GinCz/WordPress/tree/main/plugins) directory:

| # | Plugin | Version | Purpose & Key Architecture | Source |
|---|---|---|---|---|
| 1 | **404-410-301** | `1.31` | Instant HTTP 410 Gone for retired URLs and fast 301 redirects without database overhead | [404-410-301/ ↗](https://github.com/GinCz/WordPress/tree/main/plugins/404-410-301) |
| 2 | **classic-editor-tinymce** | `1.31` | Classic WordPress Editor without Gutenberg background script overhead | [classic-editor-tinymce/ ↗](https://github.com/GinCz/WordPress/tree/main/plugins/classic-editor-tinymce) |
| 3 | **clean-head-meta** | `1.31` | Purges HTML `<head>` bloat: RSD, WLW, shortlinks, emoji scripts, generator tags | [clean-head-meta/ ↗](https://github.com/GinCz/WordPress/tree/main/plugins/clean-head-meta) |
| 4 | **disable-update-emails** | `1.31` | Suppresses system automated email notifications for background updates | [disable-update-emails/ ↗](https://github.com/GinCz/WordPress/tree/main/plugins/disable-update-emails) |
| 5 | **image-resizer** | `1.31` | Dynamic on-the-fly optimization and dimension scaling for media uploads | [image-resizer/ ↗](https://github.com/GinCz/WordPress/tree/main/plugins/image-resizer) |
| 6 | **translit-cyr-lat** | `1.31` | SEO-compliant transliteration of Cyrillic permalinks and media filenames | [translit-cyr-lat/ ↗](https://github.com/GinCz/WordPress/tree/main/plugins/translit-cyr-lat) |
| 7 | **wp-allow-html-cats** | `1.31` | Sanitized raw HTML support in taxonomy and category descriptions | [wp-allow-html-cats/ ↗](https://github.com/GinCz/WordPress/tree/main/plugins/wp-allow-html-cats) |
| 8 | **wp-auto-sku** | `1.31` | Automated deterministic SKU generator for WooCommerce products | [wp-auto-sku/ ↗](https://github.com/GinCz/WordPress/tree/main/plugins/wp-auto-sku) |
| 9 | **wp-bulk-delete-clean** | `1.31` | Batch cleanup of post revisions, spam comments, and stale transients | [wp-bulk-delete-clean/ ↗](https://github.com/GinCz/WordPress/tree/main/plugins/wp-bulk-delete-clean) |
| 10 | **wp-online-counter** | `1.31` | Lightweight real-time active visitor counter without external trackers | [wp-online-counter/ ↗](https://github.com/GinCz/WordPress/tree/main/plugins/wp-online-counter) |
| 11 | **wp-seo-micro** | `1.31` | Ultra-fast SEO metadata engine (OpenGraph, title/desc, canonical) with zero table bloat | [wp-seo-micro/ ↗](https://github.com/GinCz/WordPress/tree/main/plugins/wp-seo-micro) |
| 12 | **wp-simple-post-order** | `1.31` | Drag & Drop ordering for posts, pages, and custom post types | [wp-simple-post-order/ ↗](https://github.com/GinCz/WordPress/tree/main/plugins/wp-simple-post-order) |
| 13 | **wp-test-email-micro** | `1.37` | Mail delivery diagnostics: SMTP, SPF, DKIM (2048-bit), DMARC validation with instant test dispatch | [wp-test-email-micro/ ↗](https://github.com/GinCz/WordPress/tree/main/plugins/wp-test-email-micro) |

---

## 🔄 Native Auto-Update Pipeline

Every plugin includes the embedded `vladimir-ai-updater.php` client, integrating directly into WordPress Core's native update interface:

1. **Header Declaration:** `Update URI: https://vladimir-ai.updates/<slug>`.
2. **Centralized Version Manifest:** [`plugins/updates.json` ↗](https://github.com/GinCz/WordPress/blob/main/plugins/updates.json).
3. **Production Release Archives:** [`plugins/zips/` ↗](https://github.com/GinCz/WordPress/tree/main/plugins/zips).

---

## 📁 Repository Structure

```
WordPress/
├── README.md                      # Master repository manifest
└── plugins/                       # Production plugin suites with source trees
    ├── 404-410-301/               # Broken link & HTTP 410 Gone manager
    ├── classic-editor-tinymce/    # Classic Editor + extended TinyMCE controls
    ├── clean-head-meta/           # HTML head sanitizer
    ├── disable-update-emails/     # Automated notification silencer
    ├── image-resizer/             # On-the-fly image optimization
    ├── translit-cyr-lat/          # Clean permalink transliteration
    ├── wp-allow-html-cats/        # HTML in category descriptions
    ├── wp-auto-sku/               # WooCommerce SKU generator
    ├── wp-bulk-delete-clean/      # Database vacuum & revision cleaner
    ├── wp-online-counter/         # Real-time online visitor counter
    ├── wp-seo-micro/              # Lightweight SEO meta engine
    ├── wp-simple-post-order/      # Drag & Drop content reordering
    ├── wp-test-email-micro/       # Mail compliance diagnostics & test dispatch
    ├── _shared/                   # Shared i18n & updater modules
    ├── zips/                      # Production release ZIP bundles
    └── updates.json               # Version manifest for native WordPress updater
```

<br/>

---

<br/>

<div align="center">

<h3>= Rooted by VladiMIR + AI =</h3>

<br/>

<a href="https://github.com/GinCz" target="_blank" rel="noopener noreferrer"><img src="https://img.shields.io/badge/GitHub-181717?style=flat&logo=github&logoColor=white" alt="GitHub" /></a>
&nbsp;&nbsp;<b>|||</b>&nbsp;&nbsp;
<a href="https://www.linkedin.com/in/gincz/" target="_blank" rel="noopener noreferrer"><img src="https://img.shields.io/badge/LinkedIn-0A66C2?style=flat&logo=linkedin&logoColor=white" alt="LinkedIn" /></a>

<br/><br/>

<sub>WordPress &copy; 2026. VladiMIR + AI</sub>

</div>
