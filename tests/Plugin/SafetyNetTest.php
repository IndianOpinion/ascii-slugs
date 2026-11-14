<?php

declare(strict_types=1);

namespace IndianOpinion\AsciiSlugs\Tests\Plugin;

/**
 * PRD 4.7 and acceptance tests 45 to 47.
 */
final class SafetyNetTest extends PluginTestCase
{
    private const BHARAT = '%e0%a4%ad%e0%a4%be%e0%a4%b0%e0%a4%a4';

    public function testAsciiSlugIsLeftAlone(): void
    {
        self::assertSame('hello', $this->plugin->filterUniquePostSlug('hello', 0, 'publish', 'post', 0, 'hello'));
        self::assertSame(0, $this->sluggerBuilds);
    }

    public function testPostTypeOutsideTheListIsLeftAlone(): void
    {
        foreach (['revision', 'nav_menu_item', 'wp_block', 'unregistered'] as $type) {
            self::assertSame(self::BHARAT, $this->plugin->filterUniquePostSlug(self::BHARAT, 0, 'publish', $type, 0, self::BHARAT), $type);
        }
        add_filter('ascii_slugs_post_types', static fn (): array => ['news']);
        self::assertSame(self::BHARAT, $this->plugin->filterUniquePostSlug(self::BHARAT, 0, 'publish', 'post', 0, self::BHARAT));
        self::assertSame('bharat', $this->plugin->filterUniquePostSlug(self::BHARAT, 0, 'publish', 'news', 0, self::BHARAT));
    }

    public function testLiveSlugStays(): void
    {
        $post = \WpStub::seedPost(['post_title' => 'भारत', 'post_name' => self::BHARAT]);
        self::assertSame(self::BHARAT, wp_unique_post_slug(self::BHARAT, $post->ID, 'publish', 'post', 0));
        self::assertSame([], \WpStub::actionsNamed('ascii_slugs_generated'));
    }

    public function testPreEncodedSlugOnANewPostIsReplacedAndMadeUnique(): void
    {
        // Another plugin removed every sanitize_title callback, ours included.
        remove_all_filters('sanitize_title');
        \WpStub::seedPost(['post_title' => 'Bharat', 'post_name' => 'bharat']);

        $post = $this->insertPost(['post_title' => 'भारत', 'post_name' => self::BHARAT]);
        self::assertSame('bharat-2', $post->post_name);
        self::assertCount(1, \WpStub::actionsNamed('ascii_slugs_generated'), 'The re-entry guard must stop a second pass.');
    }

    public function testExistingPostUsesItsStoredTitleAndDate(): void
    {
        $post = \WpStub::seedPost([
            'post_title' => 'चंद्रयान मिशन की पूरी कहानी',
            'post_name' => 'old-ascii',
            'post_date_gmt' => '2025-01-05 10:00:00',
        ]);
        $cut = '%e0%a4%9a%e0%a4%82';
        self::assertSame('chandrayan-mishan-ki-puri-kahani', $this->plugin->filterUniquePostSlug($cut, $post->ID, 'publish', 'post', 0, $cut));

        $thai = \WpStub::seedPost([
            'post_title' => 'ข่าวด่วนวันนี้',
            'post_name' => '',
            'post_date_gmt' => '2025-01-05 10:00:00',
        ]);
        add_filter('ascii_slugs_options', static fn (array $o): array => ['intlMode' => 'never'] + $o);
        $encoded = '%e0%b8%82';
        $slug = $this->plugin->filterUniquePostSlug($encoded, $thai->ID, 'publish', 'post', 0, $encoded);
        self::assertIsString($slug);
        self::assertStringStartsWith('post-20250105-', $slug);
    }

    public function testDecodedSlugThatIsBrokenUtf8StillGivesASafeSlug(): void
    {
        $cutMidCharacter = '%e0%a4%ad%e0%a4';
        $slug = $this->plugin->filterUniquePostSlug($cutMidCharacter, 0, 'publish', 'post', 0, $cutMidCharacter);
        self::assertIsString($slug);
        self::assertMatchesRegularExpression('/^post-20261112-[0-9a-f]{8}$/', $slug);
        self::assertCount(1, \WpStub::actionsNamed('ascii_slugs_error'));
    }

    public function testNonStringSlugPassesThrough(): void
    {
        self::assertNull($this->plugin->filterUniquePostSlug(null));
    }
}
