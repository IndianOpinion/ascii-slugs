<?php

declare(strict_types=1);

namespace IndianOpinion\AsciiSlugs\Tests\Plugin;

use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;

/**
 * PRD 4.6 and acceptance tests 35 to 44 and 60.
 */
final class SanitizeTitleTest extends PluginTestCase
{
    public function testQueryContextIsLeftAlone(): void
    {
        self::assertSame('भारत', $this->plugin->filterSanitizeTitle('भारत', 'भारत', 'query'));
        self::assertSame(0, $this->sluggerBuilds);
        // Through core: the lookup keeps the encoded form so old URLs resolve.
        self::assertSame('%e0%a4%ad%e0%a4%be%e0%a4%b0%e0%a4%a4', sanitize_title('भारत', '', 'query'));
    }

    public function testAsciiTitleIsTheFastPath(): void
    {
        self::assertSame('Hello World', $this->plugin->filterSanitizeTitle('Hello World', 'Hello World', 'save'));
        self::assertSame('%e0%a4%ad', $this->plugin->filterSanitizeTitle('%e0%a4%ad', '%e0%a4%ad', 'save'));
        self::assertSame(0, $this->sluggerBuilds, 'The library must not be called for ASCII titles.');
    }

    public function testEnglishWithCurlyQuotesAndDashesIsLeftToCore(): void
    {
        $title = "It\u{2019}s a deal \u{2013} today";
        self::assertSame($title, $this->plugin->filterSanitizeTitle($title, $title, 'save'));
        self::assertSame('its-a-deal-today', sanitize_title($title));
        self::assertSame(0, $this->sluggerBuilds);
    }

    public function testShouldHandleFilterOptsOut(): void
    {
        $seen = [];
        add_filter('ascii_slugs_should_handle', static function (bool $handle, string $raw, string $context) use (&$seen): bool {
            $seen[] = [$raw, $context];

            return false;
        }, 10, 3);
        self::assertSame('भारत', $this->plugin->filterSanitizeTitle('भारत', 'भारत', 'save'));
        self::assertSame([['भारत', 'save']], $seen);
    }

    public function testInvisibleOnlyTitleIsLeftToCore(): void
    {
        $zeroWidth = "\u{200B}\u{200C}\u{200D}";
        self::assertSame($zeroWidth, $this->plugin->filterSanitizeTitle($zeroWidth, $zeroWidth, 'save'));
        // Tatweel is encodable for core but empty once prepared.
        $tatweel = "\u{0640}\u{0640}\u{200D}";
        self::assertSame($tatweel, $this->plugin->filterSanitizeTitle($tatweel, $tatweel, 'save'));
        self::assertSame([], \WpStub::actionsNamed('ascii_slugs_generated'));
    }

    public function testPublishingStoresTheTransliteratedSlug(): void
    {
        $post = $this->insertPost(['post_title' => 'ISRO का चंद्रयान-3 मिशन']);
        self::assertSame('isro-ka-chandrayan-3-mishan', $post->post_name);

        $generated = \WpStub::actionsNamed('ascii_slugs_generated');
        self::assertCount(1, $generated);
        self::assertSame('isro-ka-chandrayan-3-mishan', $generated[0][1][0]);
        self::assertSame('ISRO का चंद्रयान-3 मिशन', $generated[0][1][1]);
    }

    public function testSlugFilterCanChangeTheSlug(): void
    {
        add_filter('ascii_slugs_slug', static fn (string $slug): string => 'hi-' . $slug);
        $post = $this->insertPost(['post_title' => 'नमस्ते']);
        self::assertSame('hi-namaste', $post->post_name);
    }

    public function testLibraryErrorGivesAHashAndTheSaveGoesAhead(): void
    {
        $broken = "\xE0\xA4\xAD\xE0\xA4";
        $post = $this->insertPost(['post_title' => $broken]);
        self::assertSame('post-20261112-' . substr(hash('sha256', $broken), 0, 8), $post->post_name);

        $errors = \WpStub::actionsNamed('ascii_slugs_error');
        self::assertCount(1, $errors);
        self::assertInstanceOf(\Throwable::class, $errors[0][1][0]);
    }

    public function testTermsAndScheduledPostsGetAsciiSlugs(): void
    {
        $term = wp_insert_term('खेल', 'category');
        self::assertSame('khel', \WpStub::$terms[$term['term_id']]['slug']);

        $scheduled = $this->insertPost(['post_title' => 'कल का मौसम', 'post_status' => 'future']);
        self::assertSame('kal-ka-mausam', $scheduled->post_name);

        $private = $this->insertPost(['post_title' => 'தமிழ்', 'post_status' => 'private']);
        self::assertSame('tamizh', $private->post_name);
    }

    public function testDraftsGetNoSlugUntilPublished(): void
    {
        $draft = $this->insertPost(['post_title' => 'भारत', 'post_status' => 'draft']);
        self::assertSame('', $draft->post_name);
        // The editor's sample permalink calls sanitize_title() on the title.
        self::assertSame('bharat', sanitize_title($draft->post_title));
        wp_update_post(['ID' => $draft->ID, 'post_status' => 'publish']);
        self::assertSame('bharat', get_post($draft->ID)?->post_name);
    }

    public function testStoredEncodedSlugNeverMovesOnUpdate(): void
    {
        $encoded = '%e0%a4%ad%e0%a4%be%e0%a4%b0%e0%a4%a4';
        $post = \WpStub::seedPost(['post_title' => 'भारत', 'post_name' => $encoded]);

        wp_update_post(['ID' => $post->ID, 'post_title' => 'भारत और दुनिया']);
        self::assertSame($encoded, get_post($post->ID)?->post_name);

        wp_update_post(['ID' => $post->ID, 'post_name' => $encoded]);
        self::assertSame($encoded, get_post($post->ID)?->post_name);
        self::assertSame([], \WpStub::$meta);
    }

    public function testHandTypedNonLatinSlugIsTransliterated(): void
    {
        $post = $this->insertPost(['post_title' => 'Old title']);
        self::assertSame('old-title', $post->post_name);

        wp_update_post(['ID' => $post->ID, 'post_name' => 'नया शीर्षक']);
        self::assertSame('naya-shirshak', get_post($post->ID)?->post_name);
        self::assertSame(['old-title'], \WpStub::$meta[$post->ID]['_wp_old_slug']);
    }

    public function testNonStringTitlesPassThrough(): void
    {
        self::assertSame(42, $this->plugin->filterSanitizeTitle(42, 42, 'save'));
        self::assertNull($this->plugin->filterSanitizeTitle(null, '', 'save'));
    }

    public function testEnabledFilterIsAKillSwitch(): void
    {
        add_filter('ascii_slugs_enabled', '__return_false_stub');
        self::assertSame('भारत', $this->plugin->filterSanitizeTitle('भारत', 'भारत', 'save'));
        $encoded = '%e0%a4%ad';
        self::assertSame($encoded, $this->plugin->filterUniquePostSlug($encoded, 0, 'publish', 'post', 0, $encoded));
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testDisabledConstantIsAKillSwitch(): void
    {
        define('ASCII_SLUGS_DISABLED', true);
        self::assertFalse($this->plugin->isEnabled());
        self::assertSame('भारत', $this->plugin->filterSanitizeTitle('भारत', 'भारत', 'save'));
        $encoded = '%e0%a4%ad%e0%a4%be%e0%a4%b0%e0%a4%a4';
        self::assertSame($encoded, $this->plugin->filterUniquePostSlug($encoded, 0, 'publish', 'post', 0, $encoded));
        $post = $this->insertPost(['post_title' => 'भारत']);
        self::assertSame($encoded, $post->post_name);
    }

    public function testDecodedStoredSlugFromTheClassicEditorIsKept(): void
    {
        // The classic editor and Quick Edit show the stored slug URL-decoded
        // (core's editable_slug filter) and send it back on every save.
        $encoded = sanitize_title_with_dashes('भारत-समाचार', '', 'save');
        $post = \WpStub::seedPost(['post_title' => 'भारत समाचार', 'post_name' => $encoded]);

        self::assertSame('भारत-समाचार', $this->plugin->filterSanitizeTitle('भारत-समाचार', 'भारत-समाचार', 'old-save'));
        $id = wp_update_post(['ID' => $post->ID, 'post_title' => 'भारत समाचार आज', 'post_name' => 'भारत-समाचार']);
        self::assertSame($post->ID, $id);

        $saved = get_post($post->ID);
        self::assertNotNull($saved);
        self::assertSame($encoded, $saved->post_name);
        self::assertArrayNotHasKey($post->ID, \WpStub::$meta, 'No old-slug record: the URL did not move.');
    }

    public function testDecodedStoredTermSlugFromTheTermEditScreenIsKept(): void
    {
        // Core's own pre_term_slug callback.
        add_filter('pre_term_slug', 'sanitize_title', 10, 1);
        $encoded = sanitize_title_with_dashes('खेल', '', 'save');
        \WpStub::$terms[1] = ['name' => 'खेल', 'slug' => $encoded, 'taxonomy' => 'category'];
        \WpStub::$terms[2] = ['name' => 'खेल', 'slug' => $encoded, 'taxonomy' => 'post_tag'];

        // The edit screen sends the slug back decoded with a new name.
        self::assertIsArray(wp_update_term(1, 'category', ['name' => 'खेल जगत', 'slug' => 'खेल']));
        self::assertSame($encoded, \WpStub::$terms[1]['slug']);

        // A slug the editor really changed is transliterated as usual.
        wp_update_term(1, 'category', ['slug' => 'खेल-जगत']);
        self::assertSame('khel-jagat', \WpStub::$terms[1]['slug']);

        // A new term in another taxonomy is not held back by this one.
        self::assertSame('खेल', $this->plugin->filterPreTermSlug('खेल', 'genre'));
        self::assertSame($encoded, $this->plugin->filterPreTermSlug('खेल', 'post_tag'));
    }
}
