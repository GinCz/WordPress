# Changelog — WP Weather & Holidays

## [2026-11__1.02] - 2026-11-19
### Added & Improved
- Added horizontal country boards module `[vb_country_boards]` featuring all 11 classified boards with SVG country flags, bold uppercase typography, and dynamic current-country badge (`ВЫ ЗДЕСЬ`).
- Added responsive styling and smooth hover animation for country cards.
- Added clean top header spacing rule for home page: removes redundant `.entry-header` title and sets ~4mm (15px) margin above main banner image.

## [2026-11__1.01] - 2026-11-19
### Changed & Improved
- Changed weather layout from 2-column grid to compact 1-column vertical list (1 line per capital).
- Replaced letter country codes (cz, DE, FR, etc.) with crisp inline SVG vector flags for all 11 European capitals + Czech Republic and Russia.
- Replaced weather widget header with clean title "Погода".
- Adjusted weather typography to 11.5px so full city names fit without truncation or ellipsis.
- Enforced strict left alignment (`text-align: left !important`) across holidays banner (titles, lists, items).
- Added inline SVG flags for Czech Svátek (`cz`) and Russian holidays (`ru`).

## [2026-11__1.00] - 2026-11-19
### Initial Release
- Compact 2-column live weather widget for 11 capital cities using Open-Meteo API.
- Daily Czech Svátek (name day) indicator.
- Daily Name Days (День Ангела) list.
- European, UN international, and Russian daily holidays calendar.
- Full shortcode support: `[vb_weather]`, `[vb_holidays]`, `[vb_svatek]`, `[vb_weather_holidays]`.
