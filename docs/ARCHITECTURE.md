# Architecture

This document explains how ascii-slugs is put together, how data moves
through it, and every decision taken during the 0.1.0 build. The behaviour
it implements is specified in [PRD.md](PRD.md); section numbers below refer
to it.

## 1. Two parts

| Part | Namespace | Folder | Depends on |
|---|---|---|---|
| Library | `IndianOpinion\AsciiSlugs` | `src/`, `tables/` | PHP 8.1 and PCRE only |
| WordPress plugin | `IndianOpinion\AsciiSlugs\WordPress` | `plugin/src/`, `ascii-slugs.php`, `uninstall.php` | the library, WordPress 6.4+ |

The library never calls a WordPress function. A test runs it in a separate
PHP process with no WordPress stubs loaded, through Composer and through
`bootstrap.php` alone (`tests/Unit/FrameworkFreeTest.php`).

`bootstrap.php` registers one autoloader for both namespaces. Composer users
get the same mapping from `composer.json`. Loading the plugin namespace costs
nothing unless a plugin class is used.

### Library modules

| Class | Role |
|---|---|
| `Slugger` | Public entry point: `slug()`, `fallback()`, `fallbackForBytes()`, `unique()`, `normalise()`, `prepare()` |
| `SlugOptions` | Immutable, validated options (section 7) |
| `SlugResult` | Slug, method, coverage, scripts |
| `Transliterator` | Splits text into runs and sends each run to a table, to ICU or nowhere |
| `TransliterationResult` | ASCII text before slug rules, letter counts, scripts, ICU flag |
| `ScriptTable` | Interface for a table |
| `ArrayScriptTable` | The table implementation every built-in table uses |
| `ClockInterface`, `SystemClock`, `FixedClock` | The date for the hash fallback |
| `Exception\*` | `InvalidInputException`, `InvalidOptionException`, `SlugCollisionException`, all `AsciiSlugsException` |
| `Internal\TextPreparer` | Section 4.1 steps 2 to 6 |
| `Internal\Utf8` | UTF-8 helpers that need neither mbstring nor intl |
| `Internal\BuiltinTables` | Names and ranges of the built-in tables |
| `Internal\TableLoader` | Loads a table file on first use |
| `Internal\Engine\*` | `BrahmicEngine`, `AbjadEngine`, `AlphabetEngine` |

Classes under `Internal` are not part of the public API and may change in
any release.

### Plugin modules

| Class | Role |
|---|---|
| `Plugin` | Hooks `sanitize_title` (priority 9), `wp_unique_post_slug` (priority 20) and `pre_term_slug` (priority 9, D25); options, kill switch, post types, the shared slug builder |
| `Logger` | One-line log entries, off unless `ascii_slugs_log` is true |
| `Encoding` | Percent-encoding checks |
| `SlugRepair` | Audit and repair logic, independent of WP-CLI |
| `PostSource`, `WpdbPostSource` | Read access to posts with encoded slugs, one query per batch, plus a capped row count (D30) |
| `RepairQuery`, `RepairRow`, `RepairReport`, `EncodedPost` | Value objects |
| `Cli\Command` | `wp ascii-slugs preview|status|audit|repair` |
| `SiteHealth` | The Site Health Info section |

## 2. Data flow

### Library: `Slugger::slug()`

1. `TextPreparer::prepare()` checks UTF-8 (`InvalidInputException` if not),
   decodes entities up to three times, strips tags (D27), removes invisible
   characters and in-word quotes.
2. `TextPreparer::cap()` keeps the first 1,000 code points for
   transliteration. The hash, if needed, uses the whole prepared title.
3. `Transliterator::transliterate()`:
   1. Splits the text into code points (`preg_split` with `/u`).
   2. Gives each code point a run kind: `#ascii` below U+0080, a table name
      when a table range holds it, `#mark` for a combining mark outside any
      table (it joins the run before it, or becomes `#stray` at the start or
      after punctuation), `#unknown` for other letters and digits, and
      `#common` for everything else. Kinds are cached per character.
   3. Groups neighbours of the same kind into runs.
   4. Renders each run. ASCII passes through. `#common` becomes a space.
      `#stray` is dropped and counted as uncovered. `#unknown` goes to ICU
      when the mode allows, or is dropped. A table run is tokenised by
      greedy longest match and handed to the engine for the table type.
   5. Counts letters (Unicode L and M) and covered letters per run.
4. The text is lower-cased, every run outside `[a-z0-9]` becomes one hyphen,
   and hyphens are trimmed.
5. The slug is cut to `maxLength` (section 4.3.5).
6. The readability floor decides between the slug and the hash fallback.

### Plugin: main path (`sanitize_title`, section 4.6)

```
context is not "save"          -> return input
raw title is plain ASCII       -> return input            (one byte-class test)
kill switch on                 -> return input
core output has no %80-%ff     -> return input            (skipped for broken UTF-8)
ascii_slugs_should_handle false-> return input
prepared title is empty        -> return input
otherwise                      -> library slug -> ascii_slugs_slug -> clean again
                                  -> ascii_slugs_generated -> log line
```

Core's own sanitiser at priority 10 then receives an ASCII slug and leaves
it as it is. Uniqueness is core's job.

### Plugin: safety net (`wp_unique_post_slug`, section 4.7)

```
re-entry guard set, or slug has no %80-%ff  -> return slug
kill switch on, or post type not covered    -> return slug
post exists and its stored slug is this one -> return slug (live URL stays)
source text = stored title, else rawurldecode(original slug)
date        = post_date_gmt when set
new slug    = makeSlug(source, date)
return wp_unique_post_slug(new slug, ...)   (guard set while it runs)
```

### Plugin: repair (section 4.9)

`SlugRepair` walks the primary key in batches through a `PostSource`. Each
batch is one prepared query (`ID > last AND post_name REGEXP ... ORDER BY
ID LIMIT n`), and rows are checked again in PHP. Between batches the runtime
object cache is flushed. Each post is saved with `wp_update_post()`, so core
records the old slug and its old-slug redirect answers the old URL with a 301.

## 3. Table format

Every table file under `tables/` returns:

```php
[
    'name' => 'devanagari',
    'type' => 'brahmic',          // brahmic | abjad | alphabet
    'ranges' => [[0x0900, 0x097F], [0xA8E0, 0xA8FF]],
    'rules' => [...],
    'classes' => ['consonant' => ["\u{0915}" => 'k', ...], ...],
]
```

Keys are written as escapes with the Unicode name in a trailing comment, so
combining marks are readable in review. Multi-code-point keys hold the
decomposed forms (section 4.1.7) and special clusters such as `ज्ञ`.

`Internal\BuiltinTables::RANGES` repeats the ranges, so the transliterator
knows which table owns a code point without loading any table. A test checks
that the index and the files agree.

Brahmic classes: `consonant`, `dead` (a consonant with no inherent vowel:
Malayalam chillus, Bengali khanda ta, Tamil aytham), `vowel` (independent),
`sign` (matra), `virama`, `anusvara`, `chandrabindu`, `visarga`, `addak`,
`mark` (dropped: nukta on its own, avagraha, accents), `digit`,
`separator`, `other` (a symbol that is a word on its own, such as `ॐ`).

Brahmic rules: `inherentVowel`, `dropFinalInherentVowel`, `anusvara`
(`assimilate` or a fixed string), `anusvaraFinal` (output for a
word-final anusvara under `assimilate`, default `n`).

Abjad classes: `consonant`, `vowel` (alif, alif madda, bari ye),
`yeh` and `waw` (context decides), `harakat`, `shadda`, `mark`, `digit`,
`separator`. Rule: `exceptions` (file name of the exception list).

Alphabet classes: `letter`, `mark`, `digit`, `separator`. Rule:
`dropCombiningDiacritics`.

### Cyrillic table (lower case shown; upper case maps the same)

`а` `a`, `б` `b`, `в` `v`, `г` `g`, `д` `d`, `е` `e`, `ж` `zh`, `з` `z`,
`и` `i`, `й` `y`, `к` `k`, `л` `l`, `м` `m`, `н` `n`, `о` `o`, `п` `p`,
`р` `r`, `с` `s`, `т` `t`, `у` `u`, `ф` `f`, `х` `kh`, `ц` `ts`, `ч` `ch`,
`ш` `sh`, `щ` `shch`, `ъ` (dropped), `ы` `y`, `ь` (dropped), `э` `e`,
`ю` `yu`, `я` `ya`, `ѐ` `e`, `ё` `e`, `ђ` `dj`, `ѓ` `gj`, `є` `ye`,
`ѕ` `dz`, `і` `i`, `ї` `yi`, `ј` `j`, `љ` `lj`, `њ` `nj`, `ћ` `c`,
`ќ` `kj`, `ѝ` `i`, `ў` `u`, `џ` `dz`, `ґ` `g`.

### Greek table (lower case shown; accented and polytonic forms map to their base letter)

`α` `a`, `β` `v`, `γ` `g`, `δ` `d`, `ε` `e`, `ζ` `z`, `η` `i`, `θ` `th`,
`ι` `i`, `κ` `k`, `λ` `l`, `μ` `m`, `ν` `n`, `ξ` `x`, `ο` `o`, `π` `p`,
`ρ` `r`, `σ` and `ς` `s`, `τ` `t`, `υ` `y`, `φ` `f`, `χ` `ch`, `ψ` `ps`,
`ω` `o`. Accents, breathings and the iota subscript are dropped.

### Latin table

Every letter in U+00C0 to U+024F and U+1E00 to U+1EFF whose canonical
decomposition starts with an ASCII letter maps to that letter. Four IPA
letters common in African and Turkic orthographies are covered one by one:
`ɔ` (U+0254), `ə` (U+0259), `ɛ` (U+025B) and `ʒ` (U+0292). These are
expanded: `ß` `ss`, `æ` `ae`, `œ` `oe`, `ø` `o`, `ł` `l`, `đ` `d`, `ð` `d`,
`þ` `th`, `ı` `i`, plus `ŋ` `ng`, `ħ` `h`, `ŧ` `t`, `ĳ` `ij`, `ǆ` `dz`,
`ǉ` `lj`, `ǌ` `nj`, `ə` `e`, `ʒ` `zh` and a few more listed in the file.

### Scripts with built-in tables in 0.1.0

Devanagari, Bengali-Assamese, Gurmukhi, Gujarati, Odia, Tamil, Telugu,
Kannada, Malayalam, Arabic (Urdu rules), Cyrillic, Greek, Latin with
diacritics. Everything else (Sinhala, Meetei Mayek, Ol Chiki, Tibetan, Thai,
Chinese, Japanese, Korean, Hebrew and so on) goes to ICU when `intl` is
present, and to the hash fallback when it is not.

## 4. Decisions

### Rulings on the PRD's open questions (section 13)

**D1. Hand-typed non-Latin slugs are transliterated by default.** An editor
who types a slug is changing the URL anyway, and core records the old slug.
Keeping a percent-encoded slug would bring back the 200-byte cut this
project exists to stop. Sites that want typed slugs kept return false from
`ascii_slugs_should_handle`; there is no extra option, because the filter
already covers it and every option is one more thing to explain. Note that
`sanitize_title` gets no flag that says "this is a typed slug", so the
filter callback has to decide from the raw title (see
`examples/wordpress-filters.php`).

**D2. The fallback date is the post's GMT publish date when it is known,
otherwise the date the slug is made.** The library takes an optional date:
`Slugger::slug($title, $date)` and `Slugger::fallback($title, $date)`. The
plugin passes `post_date_gmt` wherever it knows the post: in the safety net
for an existing post, and in `repair` and `audit`. On the main path,
`sanitize_title` carries no post, so the date is the day the slug is made.
The date never changes once a slug is stored, because stored slugs are never
rebuilt: they are ASCII and take the fast path on every later save. A
zero date (`0000-00-00 00:00:00`, drafts) counts as unknown.

**D3. `minCoverage` stays at 0.5.** `tests/fixtures/mixed-headlines.php`
holds 45 headlines written for this project, in covered and uncovered
scripts and mixes of them. With
the default and `intl` off, every headline in a covered script gives a
readable slug, and the six that are mostly in scripts with no table (Thai,
Japanese, Korean, Sinhala, Hebrew with one English word) give the hash.
Headlines that are mostly English with a short uncovered name keep the
English words (`Modi meets 习近平 in Kazan` gives `modi-meets-in-kazan`).
A higher default would hash those; a lower one would let one English word
stand in for a Thai headline. The test suite checks every expected slug.

**D4. Urdu uses the simple `y`/`i` and `w`/`o` rule plus an exception list.**
`tables/urdu-words.php` holds 20 common words (`اردو` `urdu`, `اور` `aur`,
`میں` `mein`, `ہے` `hai` and so on). A whole word is looked up with its
short vowel marks removed, before the letter rules run. The list lives in
its own file so it can grow without touching the table; a test keeps it at
20 entries or fewer for 0.1.0. `پاکستان` and `لاہور` are deliberately not on
the list, so the PRD's examples (`pakstan`, `lahor`) still hold.

**D5. Sinhala, Meetei Mayek, Ol Chiki and Tibetan are left to `intl` in
0.1.0.** The scripts with built-in tables are listed in section 3 above, in
the README and in `wp ascii-slugs status`. A site that needs one of these
scripts without `intl` can add a table with `Transliterator::withTable()`.

**D6. No `--keep-modified` flag in 0.1.0.** Repair goes through
`wp_update_post()`, which moves the modified date and is what makes core
record the old slug and redirect it. Keeping the date would need a direct
database write plus a hand-made old-slug record, which bypasses every other
plugin's save hooks. The command help and the README say the date moves.
The flag is listed under "Future" in the README and the CHANGELOG.

**D7. Every `save` call of `sanitize_title` is handled, terms included.**
Core gives no post context in that filter, so limiting it to posts is not
possible without guessing. Term names in Indian scripts have the same
200-byte problem, and a term slug is not a live URL until the term is saved,
so handling them is safe. Existing term slugs are never touched: they are
ASCII once stored, and the one place they come back decoded (the term edit
screen and Quick Edit) is caught by D25. The README documents that terms
are covered, and `ascii_slugs_should_handle` is the opt-out.

**D8. A Site Health section is added.** It sits on the Info tab under
"ASCII Slugs" and shows the version, whether the plugin is enabled, whether
`intl` is available (with the ICU version), and how many published posts of
the covered post types still have percent-encoded slugs. The count is one
`REGEXP` query and runs only when an administrator opens the Info tab. It
is bounded on large sites (D30).

### Rules refined during the build

**D9. Word-final inherent vowel.** The PRD drops the final `a` in
Devanagari, Bengali, Gurmukhi and Gujarati. Two cases keep it: a word of one
consonant (`न` gives `na`, not `n`) and a word that ends in a conjunct whose
last letter is `r`, `y` or `v`, or is `ज्ञ` (`मित्र` `mitra`, `राष्ट्र`
`rashtra`, `सत्य` `satya`, `यज्ञ` `yagya`). Other final conjuncts drop it
(`टैक्स` `taiks`, `रिकॉर्ड` `rikord`, `धर्म` `dharm`). A word is a stretch of
the script's letters and marks; digits, dandas and spaces end it.

**D10. Word-final anusvara in Telugu and Kannada gives `m`.** `విజయం`
gives `vijayam` and `ప్రయోగం` gives `prayogam`, as they are pronounced and
usually written. Inside a word the PRD's assimilation rule still applies
(`ಬೆಂಗಳೂರು` `bengaluru`).

**D11. Bengali `য` and Odia `ଯ` give `j`.** That is how both are said and
romanised (`ରଥଯାତ୍ରା` `rathajatra`). `য়` and `ୟ` give `y`, as the PRD says.

**D12. Arabic-script details.** `أ` and alif with madda give `a`, `إ` gives
`i`, `ؤ` gives `o`, and `ئ`, `ء` and the hamza marks are dropped. Short
vowel marks do not change the `y`/`w` context. Unicode puts a short vowel
mark before the shadda, so the doubled letter is inserted back before the
vowel (`مُحَمَّد` `muhammad`). A shadda after a two-letter value such as
`sh` does not double it, to keep slugs readable. Arabic presentation forms
(U+FB50 to U+FDFF, U+FE70 to U+FEFF) are not covered.

**D13. Gurmukhi addak** doubles the first letter of the next consonant's
value (`ਪੱਕਾ` `pakka`, `ਪੱਥਰ` `patthar`), and `ch` as a whole
(`ਵਿੱਚ` `vichch`).

**D14. Signs with no base letter.** A vowel sign, virama, anusvara or
similar at the very start of a run follows a letter of another script. It
is dropped and counted as an uncovered letter (section 4.8.5).

**D15. Coverage of a text with no letters is 1.0.** Nothing was lost.
Digit-only titles are still hashed, by the "at least one letter" rule.

**D16. Script names.** Covered runs report the table name. ASCII letters
and Latin with diacritics report `latin`. Uncovered runs are named by
probing a short list of PCRE script names (`thai`, `han`, `sinhala`,
`tibetan` and so on), or `other`.

**D17. Common characters outside the tables** (spaces, punctuation,
symbols) become separators. Unmapped letters inside a covered block (rare
letters a table does not list) are dropped and count as uncovered.

### API refinements

**D18.** `Slugger::__construct()` takes an optional third argument, a
`Transliterator`, so custom tables can be used. `Transliterator::__construct()`
takes `dropFinalInherentVowel` as a second argument. `Slugger` adds
`fallbackForBytes()` (the hash over raw bytes, used by the plugin for
invalid UTF-8), `normalise()` (clean and fit a value from outside code),
and `prepare()`. `Transliterator` adds `loadedTables()` (the test hook for
lazy loading), `builtinScripts()` and `icuVersion()`.
`ArrayScriptTable::fromArray()` accepts a `$trusted` flag that skips the
per-entry checks; only the built-in loader sets it, and the test suite
checks every built-in entry instead.

**D19. The encodable check runs on the title core passes to the filter**,
which has already been through `remove_accents()`, not on the raw title.
Accented Latin titles are then left to core exactly as before. Raw titles
that are not valid UTF-8 skip the check and go straight to the error path,
so a broken importer still gets a safe hashed slug and a log line.

**D20. `ascii_slugs_slug` is applied to previews and dry runs too, but no
action fires and nothing is logged.** Audit and dry runs show what would
be stored without polluting metrics.

**D21. Repair details.** Posts with an empty title are skipped (there is
nothing to build from). "Likely cut" means 192 bytes or more: the longest
encoded code point is 9 bytes and core cuts on a code point boundary, so a
cut slug ends within 8 bytes of 200. Between batches the plugin calls
`wp_cache_flush_runtime()`, which clears only the in-memory cache, rather
than `wp_cache_flush()`, which would empty a shared persistent cache on a
live site. The reported new slug is read back after the save, so it shows
core's `-2` suffix when there was one.

**D22. WP-CLI output.** `preview` accepts `table` and `json`; `audit`
accepts `table`, `csv`, `json` and `count`; `repair` accepts `table`, `csv`
and `json`. Bad arguments end with `WP_CLI::error()` (exit code 1) before
anything is read or written.

**D23. Logging.** Notices about filters that returned the wrong type name
the filter and never a title. Error lines hold the exception class and its
message with non-ASCII bytes replaced; library messages never contain the
title.

**D24. Static analysis.** PHPStan runs at level 8 over the library and the
plugin, using `php-stubs/wordpress-stubs` and `php-stubs/wp-cli-stubs`,
with `phpVersion` pinned to 8.1 to 8.3 so a call to an 8.2+ function fails
the check. Tests use their own small stand-ins in `tests/stubs/`.

### Decisions taken at code review

**D25. Stored slugs that come back decoded.** Core's `editable_slug` filter
URL-decodes a stored slug for display, and the classic editor's slug box,
Quick Edit, the term edit screen and term Quick Edit all send that decoded
value back on every save. For posts, `wp_insert_post()` checks the round
trip itself in the `old-save` context, which the main path ignores, so the
stored slug is kept (a test models it). For terms, `wp_update_term()` runs
the value through `pre_term_slug`, then `sanitize_title` in the `save`
context, and the main path would transliterate it: editing a category's
description would move its URL with no redirect. The plugin therefore adds
a third core hook, `pre_term_slug` at priority 9: when a non-ASCII value,
encoded the way core encodes it, is the stored slug of a term in the same
taxonomy, it returns that encoded slug, which the main path then passes as
ASCII. A slug the editor really changed is still transliterated. The cost
is one cached term lookup, only for non-ASCII term slugs. This extends PRD
5.2, which lists two hooks.

**D26. Repair lifts kses for its one save.** `wp_update_post()` saves the
whole post again, and WP-CLI usually runs with no user, so core's kses
filters are active and would strip iframes, scripts and embeds from the
stored content. Repair changes only the slug, so it calls
`kses_remove_filters()` before the save and `kses_init_filters()` after it,
only when they were on. There is no capability check because there is no
user: the command needs shell access (PRD section 8).

**D27. Tags are stripped by pattern, not with `strip_tags()`.** PHP's
`strip_tags()` treats any `<` as the start of a tag and drops everything up
to the next `>`, or to the end: `Sensex 3<5 भारत` lost its Hindi words.
The preparer removes only real tags (`<` then a letter or `/`) and
comments; text inside tags is kept, as before.

**D28. Table refinements.** `জ্ঞ`, `જ્ઞ` and `ଜ୍ଞ` give `gy`, like `ज्ञ`
(PRD 4.2.5). Malayalam `ന്റ` gives `nt` and `റ്റ` gives `tt`, as written in
practice (`എന്റെ` `ente`). Kannada U+0CDE (named FA, but the letter is
LLLA) gives `zh`, and the nakaara pollu letters in Kannada and Telugu are
dead `n`. Devanagari candra long e gives `e`, the Sindhi and Marwari letters
U+0978 to U+097F are mapped, and U+0979 gives `zh`. Signs that were filed
as letters (Kannada siddham, Malayalam vedic anusvara) are dropped marks,
and the Malayalam dot reph gives `r`. The decomposed Tamil `ஔ` and the
decomposed Arabic U+06C0 were added (PRD 4.1.7).

**D29. Urdu exceptions match Arabic letter forms.** Urdu typed on an
Arabic keyboard uses `ه`, `ي`, `ى` and `ك` for `ہ`, `ی` and `ک`. The
exception lookup folds those, so `هے` finds `ہے`. Run-initial letters that
the table drops (Arabic `ع`, the avagraha) count as covered: only true
combining marks at the start of a run are treated as stray (PRD 4.8.5).

**D30. Site Health count is bounded.** `post_name REGEXP` cannot use an
index. Measured with MySQL 8.0 on a 2 vCPU VM: 0.25 s for 100,000 posts in
memory, 36 s for 1,000,000 posts that did not fit the buffer pool, which
would hang the Info screen past PHP's time limit. Site Health first counts
published rows of the covered types on the `type_status_date` index,
stopping at the limit (0.15 s on the million-row table), and above
`ascii_slugs_site_health_scan_limit` (default 100,000) it shows "Not
counted" with the WP-CLI command to run instead. `audit --format=count`
now runs one COUNT query; before, it counted only the first `--limit`
rows, so the number to watch stopped at 100.

## 5. Performance

Measured at review on a shared 2 vCPU virtual machine, PHP 8.3, `intl`
off, JIT off, with PHP's built-in server so OPcache keeps the compiled
files between requests. Each figure is the median of 50 requests. The
title is 200 code points of Devanagari news words.

| Case | Budget (section 9) | Measured |
|---|---|---|
| ASCII title in `sanitize_title` | under 2 µs | one byte-class test, well under 1 µs |
| 200 code point Devanagari title, table loaded | under 0.5 ms | about 0.5 to 0.6 ms |
| Same, first call in a request, OPcache on | under 0.5 ms | about 1.2 ms: not met |
| Same, first call, OPcache off | | about 4 ms |
| Memory with every table loaded | under 512 KB | about 360 KB with OPcache, about 530 KB without |

The first call splits into about 0.1 ms to build the `Slugger` and its
range index, about 0.45 ms to load the classes and the Devanagari table and
build its lookup maps (0.15 ms of that is the maps), and about 0.7 ms to
transliterate with empty per-character caches. The steady-state cost is
about 2.5 µs per code point, most of it in building one token object per
code point and in the engine's per-token work. Making the first call meet
the budget needs both precomputed lookup maps in the table files and a
leaner tokenizer; it is listed as planned in the CHANGELOG. A news title is
usually 60 to 120 code points, so the real first-call cost per saved post is
nearer 0.5 to 0.8 ms, and it is paid once per save, not per page view.

## 6. Acceptance tests

| PRD test | Where |
|---|---|
| 1 to 7 | `tests/Unit/PreparationTest.php` |
| 8 to 21 | `tests/Unit/ScriptTablesTest.php` |
| 22 to 28 | `tests/Unit/SlugBuildingTest.php` |
| 24 | `tests/Unit/PropertyTest.php` |
| 29 to 34 | `tests/Unit/FallbackAndUniqueTest.php`, `tests/Unit/OptionsTest.php` |
| 35 to 44, 60 | `tests/Plugin/SanitizeTitleTest.php` |
| 45 to 47 | `tests/Plugin/SafetyNetTest.php` |
| 48 to 50, D3 fixture | `tests/Unit/MixedScriptTest.php` |
| 51 to 58 | `tests/Plugin/RepairTest.php` |
| 59, 61 | `tests/Plugin/FailureModesTest.php` |
| 5.4, 11, D8 | `tests/Plugin/CliAndSiteHealthTest.php` |
| D25 | `tests/Plugin/SanitizeTitleTest.php` |
| D26, D30 (audit count) | `tests/Plugin/RepairTest.php` |
| D27 | `tests/Unit/PreparationTest.php` |
| D28, D29 | `tests/Unit/ScriptTablesTest.php`, `tests/Unit/PreparationTest.php` |
| D30 (Site Health) | `tests/Plugin/CliAndSiteHealthTest.php` |
| Custom tables | `tests/Unit/CustomTableTest.php` |
| Library without WordPress | `tests/Unit/FrameworkFreeTest.php` |

Test 6 asks for "within the performance budget"; the suite asserts a loose
ceiling (250 ms for 5,000 code points) so it does not flake on busy CI
runners. The numbers in section 5 were measured by hand.
