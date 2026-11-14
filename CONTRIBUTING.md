# Contributing

Thank you for helping. This file covers setup, tests, style and how pull
requests are reviewed.

## Setup

You need PHP 8.1 or later and Composer.

```sh
git clone https://github.com/IndianOpinion/ascii-slugs.git
cd ascii-slugs
composer install
```

The `intl` extension is optional. Without it, the tests that need ICU are
skipped.

## Tests and checks

```sh
composer test      # PHPUnit: library and plugin suites
composer analyse   # PHPStan, level 8
composer lint      # PHP_CodeSniffer, PSR-12
composer check     # all three
```

Run one suite with `vendor/bin/phpunit --testsuite library` or
`--testsuite plugin`. The plugin tests run against small WordPress and
WP-CLI stand-ins in `tests/stubs/`; no WordPress install is needed.

CI runs the same checks on PHP 8.1, 8.2 and 8.3. Write code that runs on
8.1: no readonly classes, no DNF types, no `true` type, no constants in
traits.

## Style

- PSR-12, `declare(strict_types=1);` in every PHP file.
- The library (`src/`) never calls WordPress. Keep it that way.
- Typed exceptions, never silent failure, never the `@` operator.
- No `eval`, no shell, no network calls.
- Prose: short sentences, plain words, British spelling.

## Changing a table

Tables are plain PHP arrays in `tables/`. Keys are written as `\u{XXXX}`
escapes with the Unicode name in a comment. When you change a rule:

1. Add or update a test in `tests/Unit/ScriptTablesTest.php`.
2. Run the whole suite. The headline fixture in
   `tests/fixtures/mixed-headlines.php` shows the effect on real-looking
   titles; update an expectation only if the new slug reads better, and say
   why in the pull request.
3. If the change is a decision others should know about, add it to
   `docs/ARCHITECTURE.md`.

The Urdu exception list (`tables/urdu-words.php`) holds whole words only.

## Pull requests

- One topic per pull request. Small is better.
- Describe what changes for users, and how you tested it.
- All checks must pass. New behaviour needs a test.
- Update `CHANGELOG.md` under "Unreleased".
- A sign-off line is not required.

## Reporting bugs

Open an issue with the title text (or a similar one you can share), the
slug you got, the slug you expected, your PHP version and whether `intl` is
installed. `wp ascii-slugs preview "<title>"` prints most of this.

Security issues: see [SECURITY.md](SECURITY.md).
