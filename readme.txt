=== ASCII Slugs ===
Contributors: indianopinion
Tags: slug, permalink, transliteration, hindi, urdu
Requires at least: 6.4
Tested up to: 6.8
Requires PHP: 8.1
Stable tag: 0.1.0
License: MIT
License URI: https://opensource.org/licenses/MIT

Readable ASCII slugs for titles in Indian scripts, Urdu, Cyrillic, Greek and other non-Latin scripts, instead of percent-encoded slugs cut at 200 bytes.

== Description ==

WordPress percent-encodes a slug made from a title in Devanagari, Tamil, Urdu or another non-Latin script. Each letter costs six or nine bytes, and the slug is cut at 200 bytes without warning, usually in the middle of a word.

ASCII Slugs gives those posts and terms a short, readable ASCII slug instead. Built-in tables cover Devanagari, Bengali-Assamese, Gurmukhi, Gujarati, Odia, Tamil, Telugu, Kannada, Malayalam, Arabic script with Urdu rules, Cyrillic, Greek and accented Latin, so the intl extension is not needed. Other scripts use intl when it is installed. When a readable slug is not possible, the slug is a short dated hash such as post-20261112-3f9a1c0b.

* Existing slugs never move on a normal save. Activating, deactivating or uninstalling changes no stored slug.
* English titles are untouched: when core would not percent-encode anything, the plugin does nothing.
* `wp ascii-slugs repair` fixes slugs that are already broken. It is a dry run unless you pass --yes, and core's old-slug redirect keeps the old URLs working.
* `wp ascii-slugs audit --format=count` and a Site Health section show how many percent-encoded slugs remain.
* No settings screen and no stored data. Configuration is by filters and one constant.

Developers can use the transliteration library on its own, without WordPress. Source, documentation and issues: https://github.com/IndianOpinion/ascii-slugs

This project came out of the work on indianopinion.org and is written from scratch; it is independent of and not affiliated with any third-party plugin it integrates with.

== Installation ==

1. Upload the `ascii-slugs` folder to `/wp-content/plugins/`.
2. Activate the plugin through the Plugins screen.
3. Optional: run `wp ascii-slugs audit` to see existing percent-encoded slugs.

To use it as a must-use plugin, put the folder in `wp-content/mu-plugins/ascii-slugs/` and copy `examples/mu-plugin-loader.php` to `wp-content/mu-plugins/ascii-slugs.php`.

== Frequently Asked Questions ==

= Will my existing URLs change? =

No. Only `wp ascii-slugs repair --yes` changes stored slugs, and core redirects the old URLs to the new ones.

= Does repair change the modified date? =

Yes. Repair saves through core's normal update so that core records the old slug and redirects it, and that moves the modified date.

= Can I keep slugs that editors type by hand? =

Yes. Return false from the `ascii_slugs_should_handle` filter.

= How do I turn it off without deactivating? =

Add `define( 'ASCII_SLUGS_DISABLED', true );` to wp-config.php, or return false from `ascii_slugs_enabled`.

== Changelog ==

= 0.1.0 =
* First release.
