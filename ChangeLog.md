# ChangeLog

## 2026-10-17

- Fixed layout presets to size position arrays from the configured display elements.
- Restored straight-bar defaults for target and output displays; kept other read-only values text-only by default.
- Rendered legacy straight display settings as bars after tile reload.
- Set the module name and vendor to BB Module Klima and BBNBMe.
- Matched the PHP module class name to the Symcon module name and incremented the library build.
- Exposed the PayPal hosted donation URL as plain text because Symcon form labels escape HTML and button callbacks run as PHP.

## 2026-10-17

- Replaced heater-, ventilation-, and climate-specific settings with generic actual, target, output, additional-value, and permission sections.
- Shared the target input range with its matching actual value; made output and additional display values text-only by default.
- Added side-by-side bar presets and placed curved-scale text in the open center of each semicircle.
- Renamed target control settings and added the PayPal hosted donation button image.
- Added separate display minimum, maximum, and color midpoint settings for every numeric actual and target value.
- Added X/Y offsets for display text and grouped per-area layout presets for bars and curved scales.
- Added Symcon, classic, modern, futuristic, and eight neon color palettes.
- Added the module license and links to the repository and PayPal donation page.
