# Changelog — WP Login & Admin Branding

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
