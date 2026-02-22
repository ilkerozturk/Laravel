# Changelog

All notable changes to this project are documented in this file.

## [1.0.3] - 2026-02-22
### Changed
- Reduced the width of the "Firma" column in Lead list and added text truncation for company name/location.

## [1.0.2] - 2026-02-22
### Changed
- Root `.htaccess` updated to redirect `/` to `/index.php` as requested.
- Existing root-to-`public` fallback rewrite preserved for shared-host compatibility.

## [1.0.1] - 2026-02-22
### Fixed
- Added root fallback routing for shared-host deployments where document root points to project root instead of `public`.
- Added root `index.php` proxy that boots `public/index.php` to prevent LiteSpeed/Apache 404 on `/` and `/login`.

## [1.0.0] - 2026-02-22
### Added
- Import log management: single delete, bulk delete, and clear-all actions in Import Logs page.
- Cloud Opus API settings in Admin Settings (API key, model, endpoint, max tokens).
- Cloud Opus health-check button and backend test endpoint.
- Version tracking files: `VERSION` and `CHANGELOG.md`.

### Changed
- Demo website generation pipeline now uses Cloud Opus API instead of OpenAI.
- Company demo generation now produces and zips unique website files per prompt.
- Legacy master template dependency removed from generation flow.
- Legacy template folders removed from repository (`template/master-site`, `template/master-site-tr`).

### Fixed
- Logo upload/display path normalization and compatibility improvements.
- Root redirect behavior adjusted to avoid `/public`-based path issues.
