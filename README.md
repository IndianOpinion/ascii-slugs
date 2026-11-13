# ascii-slugs

WordPress percent-encodes slugs made from titles in Devanagari, Tamil, Urdu
and other non-Latin scripts, then silently cuts them at 200 bytes, which is
about 22 Devanagari letters and usually mid-word. ascii-slugs turns those
titles into short, readable ASCII slugs with built-in tables (no `intl`
needed), falls back to a short dated hash when it cannot, and ships a
dry-run-first repair command for slugs that are already broken.

Measured: `चंद्रयान मिशन की सफलता पर देश में जश्न` (38 code points) needs
300 bytes once percent-encoded, so WordPress stores a slug cut at 200 bytes;
ascii-slugs gives `chandrayan-mishan-ki-saphalata-par-desh-men-jashn`
(49 bytes).

It has two parts:

- **A PHP library** (`IndianOpinion\AsciiSlugs`) with no dependencies and no
  WordPress code. Use it in any PHP 8.1+ project.
- **A thin WordPress plugin** on top of it that applies the library to new
  post and term slugs, plus WP-CLI commands and a Site Health section.

This project came out of the work on indianopinion.org and is written from
scratch; it is independent of and not affiliated with any third-party plugin
it integrates with.

## Contents

- [Install](#install)
- [Quick start](#quick-start)
- [Configuration](#configuration)
- [WP-CLI](#wp-cli)
- [How it works](#how-it-works)
- [Limitations](#limitations)
- [FAQ](#faq)
- [Licence](#licence)

## Install

### Library, with Composer

```sh
composer require indianopinion/ascii-slugs
```

### Library, without Composer

Download a release, then:

```php
require '/path/to/ascii-slugs/bootstrap.php';
```

`bootstrap.php` registers an autoloader. Nothing else is needed.

### WordPress plugin

1. Copy the `ascii-slugs` folder to `wp-content/plugins/`.
2. Activate **ASCII Slugs** under Plugins. No settings screen; see
   [Configuration](#configuration).

Requires WordPress 6.4 or later and PHP 8.1 or later. Composer is not needed
at runtime. Activating it changes no stored slug.

### WordPress, as a must-use plugin

1. Copy the `ascii-slugs` folder to `wp-content/mu-plugins/ascii-slugs/`.
2. Copy [`examples/mu-plugin-loader.php`](examples/mu-plugin-loader.php) to
   `wp-content/mu-plugins/ascii-slugs.php`. It is four lines:

```php
<?php
if (!defined('ABSPATH')) {
    exit;
}
require_once __DIR__ . '/ascii-slugs/ascii-slugs.php';
```

On multisite it works per site, with no network options.

## Quick start

### Library

```php
use IndianOpinion\AsciiSlugs\Slugger;

$slugger = new Slugger();

$result = $slugger->slug('ISRO का चंद्रयान-3 मिशन');
$result->slug;      // "isro-ka-chandrayan-3-mishan"
$result->method;    // "transliterated"
$result->coverage;  // 1.0
$result->scripts;   // ["latin", "devanagari"]

$slugger->slug('தமிழ்')->slug;           // "tamizh"
$slugger->slug('لاہور میں بارش')->slug;  // "lahor-mein-barsh"
$slugger->slug('Москва')->slug;          // "moskva"

// A script with no built-in table, on a host without intl, on 12 November 2026:
$slugger->slug('ข่าวด่วนวันนี้')->slug;  // "post-20261112-459eae08"
```

Uniqueness is your job outside WordPress:

```php
$slug = $slugger->unique($result->slug, fn (string $s): bool => $repository->slugExists($s));
// "isro-ka-chandrayan-3-mishan", or "-2", "-3" ... up to 50 tries,
// then SlugCollisionException.
```

More in [`examples/`](examples): `basic.php`, `options-and-unique.php`,
`custom-table.php`. Each runs with `php examples/<file>`.

### WordPress

Activate the plugin and publish a post titled `ISRO का चंद्रयान-3 मिशन`. Its
slug is `isro-ka-chandrayan-3-mishan`. Category `खेल` gets the slug `khel`.
English titles are untouched: when core would not percent-encode anything,
the plugin does nothing.

## Configuration

### Library options (`SlugOptions`)

```php
use IndianOpinion\AsciiSlugs\SlugOptions;

$options = new SlugOptions(maxLength: 120, fallbackPrefix: 'story', intlMode: 'never');
$slugger = new Slugger($options);
```

| Option | Type | Default | Allowed | What it does |
|---|---|---|---|---|
| `maxLength` | int | `80` | 20 to 190 | Longest slug. Cut at a hyphen when possible. 190 leaves room for core's `-2` |
| `minCutPosition` | int | `20` | 1 to `maxLength` | A word-boundary cut before this position would leave a stub, so the cut is hard instead |
| `minAlnum` | int | `3` | 1 to 20 | Fewer `[a-z0-9]` characters than this gives the hash |
| `minCoverage` | float | `0.5` | 0.0 to 1.0 | Share of letters that must be transliterated, or the hash is used |
| `fallbackPrefix` | string | `post` | `^[a-z][a-z0-9]{0,19}$` | First part of the hash fallback |
| `hashLength` | int | `8` | 6 to 16 | Hex characters of SHA-256 in the fallback |
| `includeDate` | bool | `true` | | Put `yyyymmdd` (UTC) in the fallback |
| `intlMode` | string | `auto` | `auto`, `never`, `prefer` | `auto`: ICU only for scripts with no table. `never`: no ICU. `prefer`: ICU for every non-ASCII run (output then depends on the ICU version) |
| `dropFinalInherentVowel` | bool | `true` | | `भारत` gives `bharat`, or `bharata` when false. Devanagari, Bengali, Gurmukhi and Gujarati only |

Invalid values raise `InvalidOptionException`. `SlugOptions::fromArray()`
and `toArray()` convert to and from arrays, and every option has a
`with*()` method that returns a copy.

### Plugin filters and actions

| Name | Type | Arguments | Default |
|---|---|---|---|
| `ascii_slugs_enabled` | filter | `bool` | `true`, or `false` when the constant `ASCII_SLUGS_DISABLED` is `true` |
| `ascii_slugs_options` | filter | `array` of the options above | the defaults above |
| `ascii_slugs_should_handle` | filter | `bool`, raw title, context | `true` |
| `ascii_slugs_post_types` | filter | `string[]` | every registered type except `revision`, `nav_menu_item`, `customize_changeset`, `oembed_cache`, `user_request`, `wp_block` |
| `ascii_slugs_slug` | filter | slug, raw title, `SlugResult` | the library slug |
| `ascii_slugs_log` | filter | `bool` | `true` when `WP_DEBUG_LOG` is on |
| `ascii_slugs_generated` | action | slug, raw title, `SlugResult` | |
| `ascii_slugs_error` | action | `Throwable`, raw title | |
| `ascii_slugs_repaired` | action | post ID, old slug, new slug | |

Kill switch: `define('ASCII_SLUGS_DISABLED', true);` in `wp-config.php`,
or return `false` from `ascii_slugs_enabled`. Both hooks then return their
input unchanged.

A filter that returns the wrong type is ignored (and logged when logging is
on). A slug from `ascii_slugs_slug` is cleaned again to `[a-z0-9-]` and
`maxLength`; if nothing is left, the hash is used. Invalid options fall back
to the defaults. See [`examples/wordpress-filters.php`](examples/wordpress-filters.php).

The plugin stores nothing: no options, tables, post meta, transients or
files. `uninstall.php` removes nothing because there is nothing to remove,
and it never rewrites slugs back.

## WP-CLI

```sh
wp ascii-slugs preview "ISRO का चंद्रयान-3 मिशन"   # slug, method, coverage, scripts
wp ascii-slugs status                              # version, kill switch, intl, options, post types
wp ascii-slugs audit --format=count                # percent-encoded slugs still stored
wp ascii-slugs audit --post_type=post --limit=20   # list them, read-only
wp ascii-slugs repair                              # dry run: shows old -> new, writes nothing
wp ascii-slugs repair --ids=12,15 --yes            # writes
```

`audit` and `repair` take `--post_type=<list>` (default: the covered post
types), `--status=<list>` (default `publish`) and `--limit=<n>` (default
100). `repair` also takes `--ids=<list>`, `--batch=<n>` (default 50, the
runtime object cache is flushed between batches) and `--yes`.

What `repair --yes` does, so you can decide whether to run it:

- It builds each new slug from the stored post title, not from the cut slug.
- It saves through `wp_update_post()`. Core records the old slug and its
  old-slug redirect sends the old URL to the new one with a 301.
- **The post's modified date moves to now.** That is core behaviour.
- It skips pages and other hierarchical post types, because core does not
  redirect their old slugs. Use a redirect plugin for those.
- It does not purge edge caches or rebuild sitemaps. Hook
  `ascii_slugs_repaired` or purge as usual.
- It is idempotent: a second run finds nothing. If one post fails, the rest
  are still repaired and the command exits with code 1.

The number to watch is `wp ascii-slugs audit --format=count`. Once the
plugin is active it should stop growing. Site Health (Tools, Site Health,
Info, "ASCII Slugs") shows the same count and whether `intl` is available.

## How it works

1. **Prepare.** Check UTF-8, decode HTML entities (up to three passes, for
   feed titles such as `&amp;#8217;`), strip tags, drop zero-width joiners,
   direction marks, soft hyphens and Arabic tatweel, and remove apostrophes
   inside words (`Modi’s` gives `modis`).
2. **Split into runs** of one script each. Digits, spaces and punctuation
   sit between runs.
3. **Transliterate each run** with its own table. Built-in tables cover
   Devanagari, Bengali-Assamese, Gurmukhi, Gujarati, Odia, Tamil, Telugu,
   Kannada, Malayalam, Arabic script with Urdu rules, Cyrillic, Greek and
   Latin with diacritics. Each table loads on first use. Indic tables
   handle the inherent vowel, matras, virama conjuncts, anusvara, nukta
   letters and native digits. Runs in other scripts go to ICU when `intl`
   is installed, or are dropped.
4. **Clean and cut.** Lower case, `[a-z0-9]` joined by single hyphens, cut
   at a word boundary under `maxLength`.
5. **Readability floor.** The slug is used only if it has at least three
   letters or digits, at least one letter, and at least half of the title's
   letters were transliterated. Otherwise the result is
   `{prefix}-{yyyymmdd}-{hash}`, 22 characters with the defaults.

In WordPress, the plugin hooks `sanitize_title` at priority 9 (before core)
and acts only in the `save` context, only when core would percent-encode
something. Stored slugs are ASCII (`%e0%a4...`), so they take the fast path
and never move. A second hook on `wp_unique_post_slug` catches slugs that
arrive already percent-encoded, and finishes with core's own uniqueness
check. Uniqueness always comes from core.

The details, the full Cyrillic and Greek tables, and every design decision
are in [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md).

## Limitations

- Not scholarly transliteration. Long and short vowels fold together and the
  output does not round-trip.
- No Hindi medial schwa deletion: `कमला` gives `kamala`, `पहले` gives
  `pahale`.
- Urdu leaves short vowels unwritten, so most words come out as a consonant
  skeleton with long vowels (`پاکستان` gives `pakstan`). Twenty common words
  are spelled out from an exception list.
- No built-in table for Sinhala, Meetei Mayek, Ol Chiki, Tibetan, Thai,
  Chinese, Japanese, Korean or Hebrew in 0.1.0. They need `intl`, or they
  get the hash. You can add a table with `Transliterator::withTable()`.
- ICU output can differ between ICU versions. Slugs are computed once and
  stored, so this only matters if you rebuild slugs on another host.
- Pages and other hierarchical types are not repaired.
- A new post that arrives with an already percent-encoded slug and no title
  is rebuilt from the decoded slug, which may already have been cut.
- On the CLI without OPcache, the first slug that loads a table takes about
  1 to 2 ms (see ARCHITECTURE section 5).

### Future

- `repair --keep-modified`, which would need a direct write and a hand-made
  old-slug record. Not in 0.1.0 on purpose.
- More built-in tables (Sinhala, Meetei Mayek, Ol Chiki, Tibetan).

## FAQ

**Will activating it change my existing URLs?**
No. Activating, deactivating and uninstalling change no stored slug. Only
`wp ascii-slugs repair --yes` changes stored slugs, and it keeps the old URLs
working through core's redirect.

**My English titles get different slugs now?**
No. If core would not percent-encode anything (curly quotes, en dashes,
accented Latin), the plugin returns the title untouched and core does what
it always did.

**Do I need the intl extension?**
No. The scripts listed above work the same with and without it. `intl` only
adds coverage for scripts with no built-in table.

**An editor typed a Hindi slug by hand and it became ASCII. Can I keep typed
slugs?**
Yes: return `false` from `ascii_slugs_should_handle`. The filter gets the raw
text and the context; `sanitize_title` cannot tell a typed slug from a
title, so your callback decides. See `examples/wordpress-filters.php`.

**Are category and tag slugs covered?**
Yes. Every `save` call of `sanitize_title` is handled, including term names.
Use `ascii_slugs_should_handle` to opt out.

**Two posts got the same hashed slug.**
They had the same title on the same day. Core adds `-2`. The hash is not a
security device and must not be used for access checks.

**Where are the logs?**
With `WP_DEBUG_LOG` on (or `ascii_slugs_log` returning true), one line per
non-ASCII slug, for example
`ascii-slugs: method=hashed coverage=0.00 slug=post-20261112-3f9a1c0b`. Lines
never include the title.

## Contributing and security

See [CONTRIBUTING.md](CONTRIBUTING.md) and [SECURITY.md](SECURITY.md).
Questions: hello@indianopinion.org.

## Licence

MIT. See [LICENSE](LICENSE).
