# Changelog

All notable changes to this project are documented here. The format follows
[Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and the project
uses [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Planned

- `wp ascii-slugs repair --keep-modified` (see docs/ARCHITECTURE.md, D6).
- Built-in tables for Sinhala, Meetei Mayek, Ol Chiki and Tibetan.
- A faster first slug per request: the 0.5 ms budget is not met yet (about
  1.2 ms measured with OPcache, see README, Limitations).

## [0.1.0] - 2026-11-13

### Added

- Library `IndianOpinion\AsciiSlugs` with no dependencies: `Slugger`,
  `SlugOptions`, `SlugResult`, `Transliterator`, `ScriptTable`,
  `ArrayScriptTable`, clocks and typed exceptions. Usable through Composer
  or one `require` of `bootstrap.php`.
- Built-in tables for Devanagari, Bengali-Assamese, Gurmukhi, Gujarati,
  Odia, Tamil, Telugu, Kannada, Malayalam, Arabic script with Urdu rules
  (plus a 20-word Urdu exception list), Cyrillic, Greek and Latin with
  diacritics. Tables load on first use.
- Optional ICU transliteration through `intl` for other scripts, with the
  modes `auto`, `never` and `prefer`.
- Readability floor and a dated SHA-256 hash fallback.
- Uniqueness helper for use outside WordPress.
- WordPress plugin: `sanitize_title` main path, `wp_unique_post_slug`
  safety net, a `pre_term_slug` guard that keeps stored term slugs when the
  term edit screen sends them back decoded, filters and actions for every
  default, kill switch constant `ASCII_SLUGS_DISABLED`.
- WP-CLI commands `preview`, `status`, `audit` and `repair` (dry run by
  default).
- Site Health Info section with `intl` availability and the count of
  percent-encoded slugs, skipped above 100,000 published posts
  (`ascii_slugs_site_health_scan_limit`).
