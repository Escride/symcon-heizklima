# ChangeLog

## 2026-10-17

- Fixed layout presets to size position arrays from the configured display elements.
- Restored straight-bar defaults for target and output displays; kept other read-only values text-only by default.
- Rendered legacy straight display settings as bars after tile reload.
- Added per-variable outside/inside bar text placement and adjustable bar inclination; side labels follow the bar angle.
- Made the actual-value display a bar in appearance defaults and made the Symcon palette background transparent.
- Moved tile background settings into the Kachel panel and clarified the bar rotation control in degrees.
- Changed display X/Y coordinates to the upper-left corner of each element's outer bounds, including rotated bar bounds.
- Fixed target input on rotated bars by mapping pointer positions back to the bar's unrotated local coordinates.
- Set the module name and vendor to BB Module Klima and BBNBMe.
- Matched the PHP module class name to the Symcon module name and incremented the library build.
- Exposed the PayPal hosted donation URL as plain text because Symcon form labels escape HTML and button callbacks run as PHP.
- Added a per-tile JSON string output for shared visual configuration, including media IDs, plus selectable global configuration with per-section local overrides.
- Kept value and permission variable bindings local, and removed the visible permission status while retaining its target-control lock.

## 2026-10-17

- Replaced heater-, ventilation-, and climate-specific settings with generic actual, target, output, additional-value, and permission sections.
- Shared the target input range with its matching actual value; made output and additional display values text-only by default.
- Added side-by-side bar presets and placed curved-scale text in the open center of each semicircle.
- Renamed target control settings and added the PayPal hosted donation button image.
- Added separate display minimum, maximum, and color midpoint settings for every numeric actual and target value.
- Added X/Y offsets for display text and grouped per-area layout presets for bars and curved scales.
- Added Symcon, classic, modern, futuristic, and eight neon color palettes.
- Added the module license and links to the repository and PayPal donation page.
