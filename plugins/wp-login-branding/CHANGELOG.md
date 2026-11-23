# Changelog — WP Login & Admin Branding

## [2026-11__1.04] - 2026-11-23
### Default Dimensions & Deep Ocean Preset
- **Deep Ocean as Default**: Switched default authorization page canvas to the atmospheric «Deep Ocean» (Глубокий океан) gradient preset featuring midnight sapphire and neon cyan tones.
- **Enlarged Logo Default Boundaries**: Increased default max width to `400px` (was 260px) and max height to `150px` (was 65px), providing generous HD room for horizontal emblems and prominent brand marks.
- **Sanitization & Settings Form Sync**: Synchronized input fallback clamps and helper notes across admin settings.

## [2026-11__1.03] - 2026-11-23
### Turnkey Admin Settings Panel, Default Site Logo Auto-Detection & Visual FX Customizer
- **Default Site Logo Engine**:
  - Automatically detects site logo from theme mods (`site_logo` in Flatsome/Astra), WordPress core Customizer (`custom_logo`), site options (`site_logo`), or site icon fallback (`site_icon`).
  - Displays detected logo status, theme source, and image preview directly on the settings page.
  - Options: Auto-detect default site logo (Recommended), upload custom logo image via WP Media Uploader, text badge mode, or hide logo.
  - Configurable max width (px) and height (px), plus custom link destination URL.
- **Background Customization Suite**:
  - 6 Curated Premium Presets: Cosmic Purple (Signature VladiMIR+AI), Deep Ocean, Carbon Graphite Obsidian, Cyber Emerald, Sunset Aurora, and Modern Frost Glass.
  - Custom CSS Gradient input support (`linear-gradient`, `radial-gradient`).
  - Custom Background Image with WP Media Uploader, overlay color picker, and 0-90% opacity slider.
- **Visual FX & Card Glassmorphism**:
  - Hardware-accelerated entrance animations: Fade In & Slide Up, Scale Zoom In, Neon Pulse Glow, or static.
  - Ambient dynamic effects: Glowing Breathing Orb, Floating Bokeh Particles, or clean static.
  - Card styles: Frosted Glass Light, Dark Obsidian Frost, and Solid White.
  - Dynamic Accent Color Picker for buttons, focus rings, and glowing highlights.
- **Admin Settings Hub**:
  - Clean, modern administration interface under `Settings -> Брендинг входа` (`options-general.php?page=wp-login-branding`).
  - Integrated "Предпросмотр входа" quick-access button.
  - Multi-language descriptions across 8 languages in `vladimir-ai-i18n.php`.

## [2026-11__1.02] - 2026-11-23
### Dynamic Header Brand Title & Subtitle + Background Auto-Update Engine
- **Two Clean Single Lines under Flag**:
  - Line 1: Site Title (`get_bloginfo('name')`, e.g. "ДоСкА объявлений в Германии") formatted in bold white 15px with ellipsis overflow, strictly fitting in a single line.
  - Line 2: Administration Subtitle («Вход в администрирование сайта») formatted in soft violet 12.5px, single line.
  - Preserved ultra-compact vertical footprint (<35px) preventing viewport scrolling.
- **Built-in Turnkey Auto-Update Engine**:
  - Integrated `auto_update_plugin` filter into `VB_Login_Branding` and shared `vladimir-ai-updater.php`.
  - Enables native WordPress background auto-updater without manual intervention.

## [2026-11__1.01] - 2026-11-23
### UI Polish & Compact Viewport
- **Compact Vertical Dimensions**: Reduced padding, input sizes, and margins so the entire login card comfortably fits on screen without vertical scrolling or cropping the header flag.
- **Removed Redundant Badge**: Eliminated subtitle pill under the logo with "DE" text to keep visual focus strictly on site identity.
- **Suppressed Logout Notice**: Cleaned away the bulky white "Вы вышли из системы" notice for a seamless card presentation.
- **Single-Line Footer Navigation**: Combined `Регистрация • Забыли пароль? • ← На главную` into a single horizontal glass row.

## [2026-11__1.00] - 2026-11-22
### Initial Release
- **Dynamic Site Branding & Country Awareness**:
  - Automatically fetches site logo or waving national flag from theme mods / attachments.
  - Detects portal country from domain name (`doska-de.ru` -> `🇩🇪 Германия`, `doska.cz` -> `🇨🇿 Чехия`, etc.).
  - Replaces default WordPress logo and links logo to portal homepage with site name title.
- **Modern Glassmorphism UI**:
  - Deep atmospheric ambient glow canvas with subtle CSS animation.
  - Frosted glass login card with backdrop blur and soft shadow elevation.
  - Premium gradient action buttons and custom input focus rings.
  - Floating pill navigation links for password reset and site return.
  - Unified footer copyright credit: `Designed by VladiMir + AI`.
