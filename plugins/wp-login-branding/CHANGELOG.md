# Changelog — WP Login & Admin Branding

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
