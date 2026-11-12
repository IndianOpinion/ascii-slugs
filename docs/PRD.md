# PRD: ascii-slugs

Status: draft for review. Version target: 0.1.0.

ascii-slugs has two parts:

- **The library.** A PHP transliteration library with no dependencies. It turns a title in any script into a short, readable ASCII slug. When it cannot do that, it returns a short hashed slug.
- **The plugin.** A thin WordPress plugin that applies the library to post and term slugs. It also ships a read-only audit and a bulk repair command for posts that already have broken slugs.

Package name: `indianopinion/ascii-slugs`. Namespace: `IndianOpinion\AsciiSlugs`. Every class, filter and command name below is a proposal. The builder may refine names, but must keep the behaviour.

---

## 1. Problem

WordPress makes a post slug from the title. When the title is not in Latin script, WordPress keeps the UTF-8 bytes and percent-encodes them. A Devanagari, Telugu, Tamil, Bengali, Kannada, Malayalam, Gujarati or Urdu code point is two or three bytes. Encoded, each byte costs three ASCII characters (`%e0`), so one code point costs six or nine bytes of slug.

The slug column holds 200 bytes, and WordPress cuts the encoded slug at 200 bytes without warning. A Devanagari title keeps only about 22 code points (vowel signs count as code points). The URL ends in the middle of a word. Nothing errors and no log line is written. The broken URL is then shared, indexed and linked for good.

Worked example: a Hindi headline of 40 code points needs 360 bytes once encoded. WordPress keeps 198 of them, which is the first 22 code points, and the URL stops in the middle of the third word.

Other costs of percent-encoded slugs:

- Links pasted into chat apps, email and spreadsheets show as long `%E0%A4...` strings. Some tools double-encode them or break them at the line end.
- Two titles that differ only after the 22nd code point get the same cut slug, plus a `-2` suffix. Readers cannot tell them apart.
- Log lines, analytics reports and cache keys become hard to read and hard to grep.

## 2. Goals and non-goals

### Goals

1. Every new slug that WordPress would percent-encode is replaced by an ASCII slug made only of `a-z`, `0-9` and single hyphens.
2. The slug is readable whenever it can be. Built-in tables transliterate the main Indian scripts, Arabic-script Urdu, Cyrillic, Greek and accented Latin the same way on every host.
3. When transliteration is not possible, the slug is a short hash built from the title. It is never empty, never percent-encoded and never longer than the configured limit.
4. A slug that already exists on a live post is never changed by normal saves. Live URLs do not move.
5. A separate, explicit repair command can fix existing percent-encoded post slugs. It runs a dry run by default and keeps old URLs working through WordPress's own old-slug redirect.
6. The `intl` PHP extension is optional. With `intl`, scripts that have no built-in table can also be transliterated. Without it, those scripts fall back to the hash.
7. The library works outside WordPress with one `require`, or through Composer.

### Non-goals

- Scholarly or reversible transliteration. The output is a URL slug, not ISO 15919. It drops diacritics and does not round-trip.
- Phonetic accuracy beyond simple rules. For example, Hindi medial schwa deletion is out of scope: `कमला` gives `kamala`, not `kamla`.
- Translating titles. The slug is the same words in Latin letters, not English words.
- Choosing slugs for English titles. When WordPress would not percent-encode anything, the plugin does not act. Core behaviour is kept byte for byte.
- Redirects beyond WordPress's own old-slug redirect. Hierarchical post types (pages) need a redirect plugin. The repair command refuses them (see 4.9).
- Purging edge caches or rebuilding sitemaps after a repair. That belongs to the site's cache and SEO tools.
- Language detection or any policy on which languages a site accepts. This project only looks at scripts, and only to build slugs.
- An admin settings screen. Configuration is by filters and one constant (see section 7).

## 3. Users and setting

- **Site operators** of WordPress sites that publish in Indian languages, Urdu, or any mix of scripts. This includes news sites that import feeds, multilingual sites and community sites. They install the plugin as a normal plugin or as a must-use plugin.
- **PHP developers** outside WordPress (static site generators, other CMSs, importers) who need readable ASCII slugs from Indian-script text. They use the library alone.
- **Editors** do nothing differently. They type the title in their own script, and the permalink box in the editor shows the ASCII slug.

The plugin runs in every request that saves a post or term: admin, REST API, XML-RPC, WP-CLI, cron imports and feed importers. The library runs anywhere PHP 8.1 or later runs. It needs PCRE with Unicode support, which PHP always has. It does not need `mbstring` or `intl`.

## 4. Behaviour

Terms used below:

- **Title**: the input text, usually a post title or a term name.
- **Slug**: the output, made of `[a-z0-9]` runs joined by single hyphens.
- **Run**: a maximal stretch of the title in one script. Common characters (digits, spaces, punctuation) attach to the run around them.
- **Covered script**: a script with a built-in table (4.2).
- **Encodable**: a string WordPress would percent-encode, which means its core sanitiser output would contain a `%` followed by two hex digits for a byte of 0x80 or higher.

### 4.1 Library: preparing the input

1. The input must be valid UTF-8. Invalid UTF-8 raises `InvalidInputException`. The library never repairs or drops bytes silently.
2. HTML entities are decoded repeatedly until the string stops changing, at most three passes. This turns `&amp;#8217;` into `&#8217;` and then into `’`. Titles from feeds often carry double-encoded entities.
3. HTML tags are stripped. Text inside them is kept.
4. These characters are removed with no separator left behind: zero-width joiner, zero-width non-joiner, zero-width space, word joiner, the byte order mark, soft hyphen, Arabic tatweel, and the left-to-right and right-to-left marks and embeddings. ZWJ and ZWNJ only control how Indic conjuncts and Urdu letters are drawn. They must not break a word.
5. Apostrophes and quote marks inside a word (`'`, `’`, `ʼ`, `‘`, `"`, `“`, `”`) are removed without a separator. `Modi’s` gives `modis`, the same result as WordPress core.
6. Only the first 1,000 code points are transliterated. That bounds the work, because no slug is longer than 190 characters. The hash (4.4) is always computed over the whole prepared title.
7. Without `intl` there is no Unicode normaliser, so the tables accept both precomposed and decomposed forms. For example, `क़` (U+0958) and `क` + nukta (U+0915 U+093C) give the same output, and so do `é` and `e` + combining acute.

### 4.2 Library: per-script transliteration tables

Each covered script has one table. A table maps letters, vowel signs, marks and digits to ASCII, and states a few script rules. The tables are PHP arrays loaded on first use, one file per script, so a request that only sees Devanagari never loads the Tamil table.

Scripts covered in 0.1.0:

| Script | Main languages | Inherent vowel | Drops word-final inherent vowel |
|---|---|---|---|
| Devanagari | Hindi, Marathi, Nepali, Sanskrit, Konkani | a | yes |
| Bengali-Assamese | Bengali, Assamese | a | yes |
| Gurmukhi | Punjabi | a | yes |
| Gujarati | Gujarati | a | yes |
| Odia | Odia | a | no |
| Tamil | Tamil | a | no |
| Telugu | Telugu | a | no |
| Kannada | Kannada | a | no |
| Malayalam | Malayalam | a | no |
| Arabic (Urdu rules) | Urdu, Kashmiri, Sindhi, Arabic, Persian | none | n/a |
| Cyrillic | Russian, Ukrainian, Bulgarian, Serbian | none | n/a |
| Greek | Greek | none | n/a |
| Latin with diacritics | all Latin-script languages | none | n/a |

Rules shared by the Brahmic (Indic) tables:

1. A consonant letter carries its inherent vowel `a`. A following vowel sign (matra) replaces it. A following virama (halant, Tamil pulli) removes it. A following consonant leaves it in place.
2. A conjunct is written as consonant, virama, consonant, so it comes out as the consonant letters joined with no vowel between them: `स्त` gives `st`.
3. For scripts marked "yes" in the table, the inherent vowel of the last consonant in a word is dropped: `भारत` gives `bharat`, not `bharata`. A word is a run of letters and marks of that script. This rule can be switched off (section 7).
4. Long and short vowels fold to the same ASCII letter: `ā` and `a` both become `a`, `ī` and `i` both become `i`, `ū` and `u` both become `u`. Vocalic r gives `ri`. `ai` and `au` stay as two letters.
5. Aspirated consonants keep the `h`: `kh`, `gh`, `ch`, `chh`, `jh`, `th`, `dh`, `ph`, `bh`. Retroflex and dental pairs fold to the same letters (`t`, `d`, `n`). `च` is `ch` (not ISO `c`), `श` and `ष` are `sh`, `ज्ञ` is `gy`.
6. Anusvara gives `m` before `p`, `ph`, `b`, `bh` or `m`, and `n` everywhere else. These are the defaults for Devanagari, Gujarati, Gurmukhi, Odia, Telugu and Kannada. Bengali `ং` gives `ng`. Malayalam `ം` gives `m`. Tamil has no anusvara.
7. Chandrabindu gives `n`. Visarga gives `h`. Avagraha is dropped. Gurmukhi addak doubles the next consonant. Tippi and bindi follow the anusvara rule.
8. Nukta letters have their own values: `क़` `q`, `ख़` `kh`, `ग़` `gh`, `ज़` `z`, `ड़` `r`, `ढ़` `rh`, `फ़` `f`, `य़` `y`. The same holds for the Bengali, Gurmukhi and Odia forms.
9. Tamil: `ழ` gives `zh`, `ற` gives `r`, `ன` gives `n`, `ஃ` (aytham) gives `h`. Grantha letters (`ஜ` `ஷ` `ஸ` `ஹ` `க்ஷ`) give `j`, `sh`, `s`, `h` and `ksh`.
10. Malayalam: chillu letters (`ൺ` `ൻ` `ർ` `ൽ` `ൾ` `ൿ`) give `n`, `n`, `r`, `l`, `l` and `k`, with no vowel. `ള` gives `l` and `ഴ` gives `zh`.
11. Bengali: `ৎ` (khanda ta) gives `t`, `য়` gives `y`, `ড়` gives `r`, `ঢ়` gives `rh`. The Assamese letters `ৰ` and `ৱ` give `r` and `w`.
12. Native digits in every Indic script give ASCII `0` to `9`. Tamil number signs for ten, hundred and thousand (`௰` `௱` `௲`) give `10`, `100` and `1000`.
13. The danda `।` and double danda `॥` are separators. `ॐ` gives `om`.

Rules for the Arabic-script table (Urdu first):

1. Consonants map one to one to readable ASCII: `ب` `b`, `پ` `p`, `ت` and `ٹ` `t`, `ث` `s`, `ج` `j`, `چ` `ch`, `ح` `h`, `خ` `kh`, `د` and `ڈ` `d`, `ذ` `z`, `ر` and `ڑ` `r`, `ز` `z`, `ژ` `zh`, `س` `s`, `ش` `sh`, `ص` `s`, `ض` `z`, `ط` `t`, `ظ` `z`, `غ` `gh`, `ف` `f`, `ق` `q`, `ک` and `ك` `k`, `گ` `g`, `ل` `l`, `م` `m`, `ن` `n`, `ں` `n`, `ہ`, `ه` and `ة` `h`.
2. `ع` and `ء` (with its seated forms) are dropped.
3. Do-chashmi he `ھ` aspirates the letter before it: `بھ` gives `bh`, `کھ` gives `kh`.
4. Alif and alif madda give `a`. `ی`, `ي` and `ى` give `y` at the start of a word or after a vowel letter, and `i` elsewhere. `ے` gives `e`. `و` gives `w` at the start of a word or after a vowel letter, and `o` elsewhere.
5. Written short vowel marks give `a` (zabar), `i` (zer) and `u` (pesh). Shadda doubles the consonant. Sukun and the other marks are dropped.
6. Arabic-Indic digits (U+0660 to U+0669) and extended Arabic-Indic digits (U+06F0 to U+06F9) give `0` to `9`. The Arabic comma, semicolon, question mark and full stop are separators.
7. Urdu usually leaves short vowels unwritten, so the output is a consonant skeleton with long vowels. `پاکستان` gives `pakstan` and `لاہور` gives `lahor`. That is an accepted limit (section 13).

Cyrillic and Greek tables use one fixed, simple romanisation each. Cyrillic: `ж` `zh`, `х` `kh`, `ц` `ts`, `ч` `ch`, `ш` `sh`, `щ` `shch`, `ю` `yu`, `я` `ya`, and the hard and soft signs are dropped. Greek: `θ` `th`, `χ` `ch`, `ψ` `ps`, `φ` `f`, and accents are dropped. The builder records the full tables in `docs/ARCHITECTURE.md`.

Latin with diacritics: combining marks are stripped, and these letters are expanded: `ß` `ss`, `æ` `ae`, `œ` `oe`, `ø` `o`, `ł` `l`, `đ` `d`, `ð` `d`, `þ` `th`, `ı` `i`.

Every table states its Unicode block ranges. The script of each code point is looked up from those ranges. Unicode categories are used only for common characters (digits, spaces, punctuation).

### 4.3 Library: building the slug

1. The prepared title is split into runs (see the terms above).
2. Each run is transliterated:
   - Covered script: its table.
   - ASCII: passed through.
   - Other script, `intl` present and the intl mode not `never`: ICU `Any-Latin; Latin-ASCII` on that run only.
   - Other script otherwise: dropped. Its letters count as not covered.
3. **Coverage** is covered letters divided by all letters. Letters are Unicode category L and combining marks attached to letters, counted per code point. ASCII letters and letters converted by a table or by ICU count as covered. Digits do not count either way.
4. The result is lower-cased. Every run of characters outside `[a-z0-9]` becomes one hyphen. Hyphens at either end are trimmed.
5. **Length.** If the slug is longer than `maxLength` (default 80), it is cut at the last hyphen at or before `maxLength`. If that hyphen sits before position `minCutPosition` (default 20), the cut would leave a stub, so the slug is cut hard at `maxLength` instead. Trailing hyphens are then trimmed.
6. **Readability floor.** The transliterated slug is used only if all of these hold:
   - It has at least `minAlnum` (default 3) characters from `[a-z0-9]`.
   - It has at least one letter. An all-digit slug clashes with date and page-number URLs, so it is not accepted.
   - Coverage is at least `minCoverage` (default 0.5). A mostly Thai title with one English word must not become just that word.
7. Otherwise the slug is the hash fallback (4.4).
8. The result reports how it was made: `ascii` (input was already plain ASCII), `transliterated`, `intl` (at least one run went through ICU) or `hashed`. It also reports the coverage and the scripts seen.

The library is pure: the same input, options and clock give the same output. Output for covered scripts does not depend on whether `intl` is installed.

### 4.4 Library: the hash fallback

1. Format: `{prefix}-{yyyymmdd}-{hash}`. With `includeDate` off, the format is `{prefix}-{hash}`.
2. `prefix` defaults to `post`. It must match `^[a-z][a-z0-9]{0,19}$`. Any other value raises `InvalidOptionException` when the options are built.
3. `yyyymmdd` is the UTC date from the injected clock. The default clock is the system clock.
4. `hash` is the first `hashLength` (default 8, allowed 6 to 16) lower-case hex characters of SHA-256 over the prepared title (4.1 steps 2 to 5), before the 1,000-code-point cap.
5. With the defaults the fallback is exactly 22 characters, for example `post-20261112-3f9a1c0b`. The total length is always `prefix + 1 + 8 + 1 + hashLength` with the date, and `prefix + 1 + hashLength` without it. That is at most 46, well under any `maxLength`.
6. The same title on the same UTC day gives the same fallback. Uniqueness is the caller's job (4.5). The hash is not a security device.
7. An empty prepared title (for example a title made only of zero-width characters) still hashes. SHA-256 of the empty string is well defined. The plugin, however, never asks for a slug from an empty title (4.6, case 5).

### 4.5 Uniqueness

1. The library's main call is stateless and does not check uniqueness.
2. For use outside WordPress, the library offers a helper that takes a slug, a callback that says whether a slug is taken, and a maximum number of tries (default 50). It tries `slug`, then `slug-2`, `slug-3` and so on. Before adding a suffix it shortens the base at a hyphen so the result still fits `maxLength`. If every try is taken, it raises `SlugCollisionException`.
3. In WordPress, uniqueness always comes from core. The plugin never invents suffixes. Core appends `-2`, `-3` and so on, and it trims the slug so the suffix fits in 200 bytes.
4. `maxLength` is capped at 190. Core's suffix then always fits without core cutting the readable part.

### 4.6 Plugin: the `sanitize_title` hook (main path)

The plugin adds a callback to the core filter `sanitize_title` at priority 9. That is before core's own `sanitize_title_with_dashes` at priority 10. Core calls this filter with the title, the raw title and a context.

1. **Context.** It acts only when the context is `save`. In the `query` context (core looks up an existing slug from a request URL) it returns the input unchanged. Old percent-encoded URLs must keep resolving.
2. **Fast path.** If the raw title is plain ASCII, it returns the input unchanged without further work. This hook runs many times per request (menus, terms, widgets), so the check must be one byte-class test.
3. **Encodable check.** It runs core's `sanitize_title_with_dashes` on the raw title in the `save` context. If the output holds no percent-encoded byte of 0x80 or higher, it returns the input unchanged. This keeps English titles with curly quotes or en dashes exactly as core makes them.
4. **Opt-out filter.** It applies `ascii_slugs_should_handle` (true by default) with the raw title and context. If that returns false, it returns the input unchanged.
5. **Empty title.** If the prepared title is empty, it returns the input unchanged so core can apply its usual fallback (the post ID).
6. **Build.** It calls the library and passes the result through the `ascii_slugs_slug` filter. It then returns that ASCII slug. Core's priority-10 sanitiser leaves an ASCII slug as it is.
7. **Errors.** If the library raises an exception (for example invalid UTF-8 from a broken importer), the plugin does not let a save fail. It returns the hash fallback built from the raw bytes, fires `ascii_slugs_error`, and logs one line when logging is on (section 11).
8. This path covers new posts on publish, scheduled and private posts, term names (categories, tags, custom taxonomies) and the sample permalink the block editor shows for a draft.
9. **Stored slugs.** When an existing post is updated, core passes the stored slug through `sanitize_title`. A stored percent-encoded slug is plain ASCII (`%e0%a4...`), so the fast path returns it unchanged. Stored slugs never move on a normal save.
10. **Typed slugs.** If an editor types a non-Latin slug by hand, it is encodable, so it is transliterated. The editor is changing the URL anyway. Core records the old slug and redirects it as usual. A site that wants typed slugs kept can return false from `ascii_slugs_should_handle` (see open question 1).

### 4.7 Plugin: the `wp_unique_post_slug` hook (safety net)

Some slugs never pass through `sanitize_title` with the raw title. Examples: a slug that arrives already percent-encoded through the REST API or an importer, a plugin that removes all `sanitize_title` callbacks, or a slug built by custom code. The plugin adds a callback to the core filter `wp_unique_post_slug` at priority 20. Core calls it with the unique slug, post ID, status, post type, parent and the original slug.

1. It acts only if the slug contains a percent-encoded byte of 0x80 or higher. Otherwise it returns the slug unchanged.
2. It acts only if the post type is in the list from `ascii_slugs_post_types`. The default is every registered post type except `revision`, `nav_menu_item`, `customize_changeset`, `oembed_cache`, `user_request` and `wp_block`.
3. **Live slugs stay.** If the post ID is above zero and the post's stored slug equals the original slug or the incoming slug, it returns the slug unchanged.
4. **Source text.** If the post ID is above zero and the stored post title is not empty, it uses that title. Otherwise it URL-decodes the original slug and uses that. The decoded slug may already have been cut at 200 bytes. That is a known limit for new posts that arrive with a pre-encoded slug.
5. It builds the slug with the library. It then runs core's `wp_unique_post_slug` on the new slug with the same arguments, behind a re-entry guard so the callback does not act on its own call, and returns the result.
6. Errors are handled as in 4.6, case 7.

### 4.8 Mixed-script titles

1. Every run goes through its own table. Latin runs and ASCII digits pass through. `ISRO का चंद्रयान-3 मिशन` gives `isro-ka-chandrayan-3-mishan`.
2. Native digits next to letters keep their place: `२०२६ चुनाव` gives `2026-chunav`.
3. Two covered Indic scripts in one title (a Hindi title quoting a Bengali name) each use their own table.
4. A covered script mixed with a script that has no table, and no `intl`: the uncovered run is dropped. If coverage then falls below `minCoverage`, the whole slug is the hash fallback.
5. Combining marks after a character of another script (bad input) are dropped. They are counted as letters not covered.

### 4.9 Existing slugs and the repair command

1. Activating the plugin changes no stored slug. Deactivating it changes no stored slug. Uninstalling it changes no stored slug.
2. `wp ascii-slugs audit` is read-only. It lists posts whose stored slug contains a percent-encoded byte of 0x80 or higher. It shows the ID, post type, status, byte length, whether the slug is at the 200-byte limit (likely cut), and the slug the plugin would give now. Options: `--post_type=<list>`, `--status=<list>` (default `publish`), `--limit=<n>` (default 100), `--format=table|csv|json|count`.
3. `wp ascii-slugs repair` runs a dry run by default. It prints the same rows as the audit, plus `old -> new`, and writes nothing. It writes only with `--yes`.
4. Repair builds the new slug from the stored post title, not from the cut slug. It saves through core's normal post update, so core keeps the old slug as an old-slug record and its old-slug redirect sends the old URL to the new one with a 301. The post's modified date moves to now. That is core behaviour, and it is documented in the command help.
5. Repair refuses hierarchical post types (pages and similar). Core's old-slug redirect does not cover them. It says why and skips them.
6. Repair takes `--post_type`, `--status`, `--ids=<list>`, `--limit` and `--batch=<n>` (default 50, with object caches flushed between batches). It is idempotent. A repaired slug is ASCII, so a second run finds nothing to do.
7. Each repaired post fires `ascii_slugs_repaired` with the post ID, old slug and new slug, so cache and sitemap tools can react.
8. If one post fails (for example an update error from another plugin), repair prints the error, counts it and goes on with the next post. It exits non-zero if any post failed.

### 4.10 Failure modes

| Failure | What happens | Open or closed, and why |
|---|---|---|
| `intl` missing | Covered scripts work the same. Other scripts give the hash fallback. | Open. A save must never fail for a missing extension. |
| ICU throws or returns false for a run | The run is treated as uncovered. The coverage rule decides. | Open. Same reason. |
| Invalid UTF-8 in the library | `InvalidInputException` | Closed in the library: no silent repair. |
| Invalid UTF-8 in the plugin | Hash fallback built from the raw bytes, plus one log line. | Open. The post still saves with a safe slug. |
| Another plugin returns non-ASCII from `ascii_slugs_slug` | The plugin cleans the value again (lower-case, `[a-z0-9-]`, length). If that leaves it empty, it uses the hash fallback. | Open, but the output is always safe. |
| A filter returns the wrong type | The plugin ignores it and uses the default. One log line when logging is on. | Open. |
| Invalid options from `ascii_slugs_options` | `InvalidOptionException` is caught, the defaults are used, and one log line is written. | Open. |
| Re-entry in the safety net | The guard returns the slug unchanged. | Open. |
| Repair hits an update error | Reported, counted, exit code 1. | Closed for that post only. |

## 5. Public interface

### 5.1 Library

All classes are `final` unless noted. `declare(strict_types=1)` is used throughout.

- `IndianOpinion\AsciiSlugs\Slugger`
  - `__construct(?SlugOptions $options = null, ?ClockInterface $clock = null)`
  - `slug(string $title): SlugResult`: builds the slug (4.3).
  - `fallback(string $title): string`: the hash fallback only (4.4).
  - `unique(string $slug, callable $isTaken, int $maxTries = 50): string`: the uniqueness helper (4.5, case 2). The callable signature is `fn(string $slug): bool`.
- `IndianOpinion\AsciiSlugs\Transliterator`
  - `__construct(string $intlMode = 'auto')`, where `$intlMode` is `auto`, `never` or `prefer`
  - `transliterate(string $text): TransliterationResult`: ASCII text before slug rules, coverage, scripts seen, scripts not covered, and whether ICU was used.
  - `withTable(ScriptTable $table): self`: returns a copy with an extra or replacement table.
  - `static intlAvailable(): bool`
- `IndianOpinion\AsciiSlugs\SlugOptions`: immutable value object with named constructor arguments and `with*()` copies. Its fields are listed in section 7. It validates every field in the constructor.
- `IndianOpinion\AsciiSlugs\SlugResult`: `slug`, `method` (`ascii`, `transliterated`, `intl` or `hashed`), `coverage` (a float from 0 to 1), and `scripts` (a list of script names).
- `IndianOpinion\AsciiSlugs\ScriptTable` (interface): `name(): string`, `ranges(): array` (code point ranges), `map(): array`, `rules(): array` (inherent vowel, final vowel drop, anusvara mode and similar).
- `IndianOpinion\AsciiSlugs\ClockInterface`: `today(): DateTimeImmutable` in UTC. A `SystemClock` and a `FixedClock` for tests are provided.
- Exceptions under `IndianOpinion\AsciiSlugs\Exception\`: `InvalidInputException`, `InvalidOptionException`, `SlugCollisionException`. All of them implement `AsciiSlugsException`.
- `bootstrap.php` at the package root registers an autoloader for use without Composer.

When `intlMode` is `prefer`, ICU is used for every non-ASCII run, including covered scripts. Output then depends on the host's ICU version. This mode exists for sites that already have ICU-made slugs and want to stay consistent with them.

### 5.2 Plugin hooks it attaches to

| Core hook | Type | Priority | Purpose |
|---|---|---|---|
| `sanitize_title` | filter | 9 | Main path (4.6) |
| `wp_unique_post_slug` | filter | 20 | Safety net (4.7) |

These are the only two core hooks the plugin attaches to for slug behaviour. WP-CLI commands are registered only when WP-CLI is loaded.

### 5.3 Plugin filters and actions it provides

| Name | Type | Arguments | Default |
|---|---|---|---|
| `ascii_slugs_enabled` | filter | `bool` | `true` (false when the constant `ASCII_SLUGS_DISABLED` is true) |
| `ascii_slugs_options` | filter | `array` of option values | values from section 7 |
| `ascii_slugs_should_handle` | filter | `bool`, raw title, context | `true` |
| `ascii_slugs_post_types` | filter | `string[]` | the list in 4.7, case 2 |
| `ascii_slugs_slug` | filter | slug, raw title, `SlugResult` | the library slug |
| `ascii_slugs_log` | filter | `bool` | `true` when `WP_DEBUG_LOG` is true, otherwise `false` |
| `ascii_slugs_generated` | action | slug, raw title, `SlugResult` | none |
| `ascii_slugs_error` | action | `Throwable`, raw title | none |
| `ascii_slugs_repaired` | action | post ID, old slug, new slug | none |

### 5.4 WP-CLI

- `wp ascii-slugs preview <title> [--format=table|json]`: shows the slug, method, coverage, scripts, and whether `intl` was used, using the site's current options.
- `wp ascii-slugs status`: plugin version, whether it is enabled, whether `intl` is available (and the ICU version), the current options, and the post types the safety net covers.
- `wp ascii-slugs audit`: see 4.9.
- `wp ascii-slugs repair`: see 4.9.

## 6. Data

- The library stores nothing.
- The plugin creates no options, no tables, no post meta, no transients and no files.
- Slugs it makes are stored by WordPress in the normal slug columns. They belong to the site, not to the plugin.
- Repair writes slugs through core. Core keeps the old-slug records it already keeps.
- `uninstall.php` exists, removes nothing (there is nothing to remove) and never rewrites slugs back. The README says so.

## 7. Configuration and sensible defaults

Library options (`SlugOptions`), which the plugin also accepts through `ascii_slugs_options`:

| Option | Type | Default | Allowed |
|---|---|---|---|
| `maxLength` | int | 80 | 20 to 190 |
| `minCutPosition` | int | 20 | 1 to `maxLength` |
| `minAlnum` | int | 3 | 1 to 20 |
| `minCoverage` | float | 0.5 | 0.0 to 1.0 |
| `fallbackPrefix` | string | `post` | `^[a-z][a-z0-9]{0,19}$` |
| `hashLength` | int | 8 | 6 to 16 |
| `includeDate` | bool | true | |
| `intlMode` | string | `auto` | `auto`, `never`, `prefer` |
| `dropFinalInherentVowel` | bool | true | applies only to the scripts marked "yes" in 4.2 |

Plugin-only settings:

| Setting | How | Default |
|---|---|---|
| Kill switch | constant `ASCII_SLUGS_DISABLED` or filter `ascii_slugs_enabled` | enabled |
| Safety net post types | `ascii_slugs_post_types` | 4.7, case 2 |
| Per-title opt-out | `ascii_slugs_should_handle` | handle all |
| Logging | `ascii_slugs_log` | follows `WP_DEBUG_LOG` |

Turning on the kill switch makes both hooks return their input unchanged. Nothing else changes.

## 8. Security and privacy

- Output contains only `[a-z0-9-]`, so it is safe in URLs, HTML attributes and file names with no escaping. The plugin still escapes everything it prints in WP-CLI tables in the usual way.
- No network calls, no shell, no `eval`, no dynamic `include` of user-controlled paths. Tables are loaded from fixed file names inside the package.
- Processing time is linear in input length and capped at 1,000 code points (4.1, case 6). Regular expressions do not nest quantifiers, so there is no catastrophic backtracking.
- The hash is not a secret and not a security control. It must not be used for access checks.
- Titles may be unpublished news. Log lines are off by default. When on, they hold the method, the coverage and the first 40 characters of the generated slug only. They never hold the raw title.
- The repair command needs WP-CLI, so it needs shell access on the host. It has no web-facing trigger, so there is no capability or nonce surface.

## 9. Performance budget

- A plain ASCII title in `sanitize_title`: one byte-class test, under 2 microseconds.
- A 200-code-point Devanagari title: under 0.5 ms on one core of a typical server, including loading the table the first time.
- Peak extra memory with every table loaded: under 512 KB.
- No database queries on the main path. The safety net adds one cached post lookup, and only for slugs that are still encoded.
- `audit` over 100,000 posts: one indexed query per batch, with constant memory per batch.

## 10. Compatibility

- PHP 8.1, 8.2 and 8.3. PCRE with Unicode support is required, and PHP always has it. `mbstring` and `intl` are optional, and the library must not call either without checking first.
- WordPress 6.4 or later. It runs as a normal plugin or a must-use plugin. A must-use loader stub is documented in the README. It works on multisite, per site, with no network options.
- Editors: the block editor, the classic editor, Quick Edit, the REST API, XML-RPC and WP-CLI `post create` all go through the same two hooks.
- Feed importers and other plugins that call `wp_insert_post` are covered without special code.
- Other plugins on `sanitize_title`: ours runs at priority 9 and returns ASCII, so later callbacks see ASCII. A plugin that runs earlier and already returns ASCII makes ours a no-op.
- Multilingual plugins that give each translation its own slug still pass through `sanitize_title`, so each translation gets its own ASCII slug.
- Hosts: shared hosts often lack `intl`. That is why covered scripts never need it.
- Caches: the plugin does not touch caches. After a repair, the old URL returns a 301 from core. Edge caches holding the old 200 response need their usual purge.
- The output of an ICU run can differ between ICU versions. Slugs are computed once and stored, so this only matters if the same title is turned into a slug again on another host.

## 11. Observability

- `wp ascii-slugs preview` shows exactly what a title would become, and why.
- `wp ascii-slugs status` shows `intl` availability and the options in force.
- `wp ascii-slugs audit --format=count` gives the number of percent-encoded slugs still in the database. It is the number to watch: after the plugin is active it should not grow.
- With logging on, one line per generated slug that was not plain ASCII, in the form `ascii-slugs: method=hashed coverage=0.00 slug=post-20261112-3f9a1c0b`. One line per error, with the exception class and message.
- `ascii_slugs_generated`, `ascii_slugs_error` and `ascii_slugs_repaired` let a site send counts to its own metrics.

## 12. Acceptance tests

Every test fixes the clock at 2026-11-12 UTC where a date shows. The expected strings below come from the rules in section 4. If a table rule is refined during the build, the builder updates the expectation and records why in `docs/ARCHITECTURE.md`.

Library: input and tables (4.1, 4.2)

1. (4.1.1) Invalid UTF-8 raises `InvalidInputException`.
2. (4.1.2) `Modi&amp;#8217;s visit` gives `modis-visit`.
3. (4.1.3) `<b>भारत</b>` gives `bharat`.
4. (4.1.4) A title with ZWJ and ZWNJ inside conjuncts gives the same slug as the same title without them.
5. (4.1.5) Curly and straight apostrophes inside a word leave no hyphen.
6. (4.1.6) A 5,000-code-point Devanagari title returns a slug of at most `maxLength` within the performance budget. For a 5,000-code-point Thai title, the fallback differs from the fallback for its first 1,000 code points.
7. (4.1.7) Precomposed `ज़िंदगी` and decomposed `ज` + nukta + `िंदगी` both give `zindagi`. NFC `é` and NFD `e` + U+0301 both give `e`.
8. (4.2 Devanagari) `नमस्ते` gives `namaste`. `भारत` gives `bharat`. `संपादक` gives `sampadak`.
9. (4.2 Bengali) `বাংলা` gives `bangla`.
10. (4.2 Gurmukhi) `ਪੰਜਾਬ` gives `panjab`.
11. (4.2 Gujarati) `ગુજરાત` gives `gujarat`.
12. (4.2 Odia) `ଓଡ଼ିଆ` gives `oria` (the nukta letter `ଡ଼` is `r`).
13. (4.2 Tamil) `தமிழ்` gives `tamizh`.
14. (4.2 Telugu) `తెలుగు` gives `telugu`.
15. (4.2 Kannada) `ಕನ್ನಡ` gives `kannada`.
16. (4.2 Malayalam) `മലയാളം` gives `malayalam`.
17. (4.2 Urdu) `پاکستان` gives `pakstan`. `لاہور` gives `lahor`. `بھارت` starts with `bh`.
18. (4.2 Cyrillic, Greek, Latin) `Москва` gives `moskva`. `Αθήνα` gives `athina`. `Straße Ørsted` gives `strasse-orsted`.
19. (4.2 digits) Native digits in each Indic script, Arabic-Indic digits and extended Arabic-Indic digits all give `0123456789`.
20. (4.2 rules) With `dropFinalInherentVowel` off, `भारत` gives `bharata`. Tamil, Telugu, Kannada, Malayalam and Odia keep a final inherent vowel either way.
21. (4.2 lazy load) Making a slug from a Devanagari title loads only the Devanagari table. Check this through a test hook on the table loader.

Library: slug, hash, uniqueness (4.3, 4.4, 4.5)

22. (4.3.2) With `intlMode` set to `never`, a Thai title gives the hash fallback. With `intl` present and the mode `auto`, it gives an ASCII slug and method `intl` (skipped when `intl` is absent).
23. (4.3.2) Output for every covered-script fixture is identical with `intlMode` set to `never` and to `auto`.
24. (4.3.4) The output never has a leading hyphen, a trailing hyphen, a double hyphen, upper case, or a character outside `[a-z0-9-]`. This is a property test over 1,000 generated mixed-script strings.
25. (4.3.5) A long Hindi title is cut at a word boundary, so the result is at most 80 characters and does not end in a hyphen. A title whose first hyphen falls after `maxLength` is cut hard at `maxLength`.
26. (4.3.6) `एक` alone (two letters, `ek`) gives the hash fallback. `२०२६` alone (digits only) gives the hash fallback.
27. (4.3.6) A Thai title with one English word, with `intl` off, gives the hash fallback because of coverage.
28. (4.3.7, 4.3.8) `SlugResult` reports `method`, `coverage` and `scripts` correctly for one ASCII, one transliterated and one hashed case.
29. (4.4.1 to 4.4.5) The fallback for a Thai title is `post-20261112-` followed by 8 hex characters, 22 characters in all. With `includeDate` off it is 13 characters. With `hashLength` 12 it is 26.
30. (4.4.2) Prefixes `Post`, `1post`, `post-x` and a 21-character prefix each raise `InvalidOptionException`.
31. (4.4.6) The same title on the same day gives the same fallback. A different title gives a different hash.
32. (4.4.7) A title made only of zero-width characters gives a valid fallback.
33. (4.5.2) `unique()` returns `slug-2` when only `slug` is taken. It shortens the base so `slug-10` still fits `maxLength`. It raises `SlugCollisionException` after `maxTries`.
34. (4.5.4) `maxLength` 191 raises `InvalidOptionException`.

Plugin: main path (4.6)

35. (4.6.1) In the `query` context, a Devanagari string comes back unchanged.
36. (4.6.2) A plain ASCII title comes back unchanged, and the library is not called (checked with a spy).
37. (4.6.3) `It’s a deal – today` comes back unchanged (core output has no high-byte encoding).
38. (4.6.4) When `ascii_slugs_should_handle` returns false, the input comes back unchanged.
39. (4.6.5) A title of only zero-width characters comes back unchanged.
40. (4.6.6) Publishing a post titled `ISRO का चंद्रयान-3 मिशन` stores the slug `isro-ka-chandrayan-3-mishan`. `ascii_slugs_slug` can change it, and `ascii_slugs_generated` fires once.
41. (4.6.7) A library exception makes the hook return a valid hash fallback, fires `ascii_slugs_error`, and the save goes ahead.
42. (4.6.8) A category named `खेल` gets the term slug `khel`. A scheduled post gets an ASCII slug.
43. (4.6.9) Updating a post whose stored slug is percent-encoded keeps that slug byte for byte.
44. (4.6.10) A slug typed by hand in Devanagari is transliterated.

Plugin: safety net, mixed scripts, repair, failures (4.7 to 4.10)

45. (4.7.1, 4.7.2) An ASCII slug, or a post type outside the list, comes back unchanged.
46. (4.7.3) An existing post whose stored slug equals the incoming encoded slug keeps it.
47. (4.7.4, 4.7.5) A new post that arrives with a pre-encoded slug (`sanitize_title` callback removed) gets an ASCII slug that core made unique (`-2` when a twin exists). The callback runs once (re-entry guard).
48. (4.8.1 to 4.8.3) The mixed fixtures from 4.8 give the slugs stated there. A Hindi title with a Bengali name uses both tables.
49. (4.8.4) A Devanagari title with a long Thai phrase and `intl` off gives the hash fallback when coverage is below 0.5, and a transliterated slug when it is above.
50. (4.8.5) Stray combining marks are dropped, and coverage goes down accordingly.
51. (4.9.1) Activating, deactivating and running `uninstall.php` leave every stored slug unchanged.
52. (4.9.2) `audit` lists only percent-encoded slugs, flags the ones at 200 bytes, and writes nothing (checked by a write spy on the stubs).
53. (4.9.3) `repair` without `--yes` writes nothing.
54. (4.9.4) `repair --yes` builds the slug from the full title, saves through the core update path, and leaves an old-slug record. The old URL is then served by core as a 301 (stubbed).
55. (4.9.5) `repair` skips a page and says why.
56. (4.9.6) A second `repair --yes` finds nothing.
57. (4.9.7) `ascii_slugs_repaired` fires with the post ID, old slug and new slug.
58. (4.9.8) One failing post is reported, the rest are repaired, and the exit code is 1.
59. (4.10) With `intl` missing (forced through the transliterator's `never` mode), covered scripts are unchanged. A non-ASCII value from `ascii_slugs_slug` is cleaned again. Invalid options fall back to the defaults with one log line. A filter that returns the wrong type is ignored.
60. (5.3, 7) With `ASCII_SLUGS_DISABLED` true, both hooks return their input unchanged.
61. (8) A log line never holds the raw title.

## 13. Open questions (answered by the builder in ARCHITECTURE.md)

1. Should slugs that editors type by hand in a non-Latin script be kept as typed, or transliterated? The default above is to transliterate, with an opt-out filter. Confirm, or add a separate option.
2. Should the date in the fallback be the post's publish date (GMT) when it is known, rather than the date the slug was made? The editor's sample permalink and the final publish can fall on different days.
3. Is 0.5 the right default for `minCoverage`? Test it against a fixture of real mixed-script headlines.
4. Urdu vowel letters: is the `y`/`i` and `w`/`o` rule good enough, or should a small exception list of common words ship (for example `اردو` to `urdu`)?
5. Should 0.1.0 also cover Sinhala, Meetei Mayek, Ol Chiki and Tibetan with tables, or leave them to `intl`?
6. Repair moves the post's modified date because it goes through core's update path. Should there be a `--keep-modified` flag, given that it would need a direct write plus a hand-made old-slug record?
7. Should `sanitize_title` handling be limited to post titles only (leaving terms alone), given that core gives no post context in that filter? The default above handles every `save` call.
8. Should the plugin add a Site Health row that shows `intl` availability and the percent-encoded slug count?

## 14. Lessons from production

1. Never move a live URL. Only make a slug for a post that has no stored slug yet. A stored percent-encoded slug is ASCII and passes through untouched.
2. Check for percent-encoding, not just for non-ASCII. A slug often reaches the save already encoded, and then it is pure ASCII.
3. Decode HTML entities before transliterating, and repeat the decode. Feed titles carry `&#8217;`, and sometimes `&amp;#8217;`.
4. Do not depend on `intl`. When transliteration relied on it alone, every title went to the hash on hosts without it. Built-in tables for the scripts you publish in make the slug the same on every host.
5. Set a readability floor. A slug with fewer than three letters or digits is worse than a hash. Use the hash.
6. Cut at a word boundary, but not when that leaves a stub. Fall back to a hard cut at the limit.
7. Leave room for the uniqueness suffix. A readable slug of 200 bytes gets its tail cut by the `-2` that core adds.
8. Always finish uniqueness with WordPress's own function after you replace a slug. Two posts with the same title on the same day get the same hash.
9. The 200-byte cut is silent. Nothing errors, so measure it: count percent-encoded slugs, and watch that the number stops growing.
10. Never act in the `query` context. Old encoded URLs must keep resolving.
11. Do not change English slugs. Curly quotes and dashes are core's business. Act only when core would percent-encode.
12. Never let slug code fail a save. On any error, store a safe hashed slug and log it.
