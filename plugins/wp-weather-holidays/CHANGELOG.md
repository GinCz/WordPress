# Changelog — WP Weather & Holidays

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
