# Changelog

All notable changes to this project are documented in this file.

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
