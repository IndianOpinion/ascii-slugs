<?php

declare(strict_types=1);

namespace IndianOpinion\AsciiSlugs\Tests\Plugin;

use IndianOpinion\AsciiSlugs\WordPress\Cli\Command;
use IndianOpinion\AsciiSlugs\WordPress\Encoding;
use IndianOpinion\AsciiSlugs\WordPress\SiteHealth;
use IndianOpinion\AsciiSlugs\WordPress\WpdbPostSource;

/**
 * PRD 5.4, section 11 and the Site Health row (ARCHITECTURE D8).
 */
final class CliAndSiteHealthTest extends PluginTestCase
{
    /**
     * @return array<string, mixed>
     */
    private function lastPayload(): array
    {
        $items = \WP_CLI::messages('items');
        $decoded = json_decode((string) end($items), true);
        self::assertIsArray($decoded);

        return $decoded;
    }

    public function testPreviewShowsSlugMethodCoverageAndScripts(): void
    {
        (new Command($this->plugin, new FakePostSource()))->preview(['ISRO का चंद्रयान-3 मिशन'], ['format' => 'json']);
        $payload = $this->lastPayload();
        self::assertSame('json', $payload['format']);
        self::assertSame([
            'slug' => 'isro-ka-chandrayan-3-mishan',
            'method' => 'transliterated',
            'coverage' => '1.00',
            'scripts' => 'latin,devanagari',
            'intl' => 'no',
        ], $payload['items'][0]);
        self::assertSame([], \WpStub::actionsNamed('ascii_slugs_generated'), 'preview must not fire actions');
    }

    public function testPreviewRejectsInvalidUtf8(): void
    {
        $this->expectException(\WpCliHalt::class);
        (new Command($this->plugin, new FakePostSource()))->preview(["\xE0"], []);
    }

    public function testStatusShowsOptionsAndPostTypes(): void
    {
        (new Command($this->plugin, new FakePostSource()))->status([], []);
        $rows = array_column($this->lastPayload()['items'], 'value', 'setting');
        self::assertSame('0.1.0', $rows['version']);
        self::assertSame('yes', $rows['enabled']);
        self::assertSame('80', $rows['maxLength']);
        self::assertSame('true', $rows['includeDate']);
        self::assertStringContainsString('devanagari', $rows['built-in tables']);
        self::assertStringContainsString('post', $rows['post types']);
        self::assertStringNotContainsString('revision', $rows['post types']);
        self::assertArrayHasKey('intl', $rows);
    }

    public function testCommandIsRegisteredOnlyUnderWpCli(): void
    {
        self::assertSame([], \WP_CLI::$commands);
    }

    public function testSiteHealthSectionShowsIntlAndEncodedCount(): void
    {
        \WpStub::seedPost(['post_title' => 'भारत', 'post_name' => '%e0%a4%ad%e0%a4%be']);
        \WpStub::seedPost(['post_title' => 'x', 'post_name' => 'x']);
        $health = new SiteHealth($this->plugin, new FakePostSource());
        $info = $health->addSection([]);
        self::assertIsArray($info);
        $fields = $info['ascii-slugs']['fields'];
        self::assertSame('1', $fields['encoded_slugs']['value']);
        self::assertSame(1, $fields['encoded_slugs']['debug']);
        self::assertContains($fields['intl']['debug'], ['yes', 'no']);
        self::assertSame('yes', $fields['enabled']['debug']);
        self::assertSame('not an array', $health->addSection('not an array'));
    }

    public function testSiteHealthIsHookedOnRegister(): void
    {
        self::assertTrue(has_filter('debug_information'));
        self::assertTrue(has_filter('sanitize_title'));
        self::assertTrue(has_filter('wp_unique_post_slug'));
    }

    public function testWpdbSourceBuildsOnePreparedQueryPerBatch(): void
    {
        $db = new \wpdb();
        $db->rows = [
            [
                'ID' => '7', 'post_name' => '%e0%a4%ad', 'post_title' => 'भ', 'post_type' => 'post',
                'post_status' => 'publish', 'post_date_gmt' => '2026-11-12 00:00:00',
            ],
            [
                'ID' => '8', 'post_name' => 'plain', 'post_title' => 'x', 'post_type' => 'post',
                'post_status' => 'publish', 'post_date_gmt' => '',
            ],
        ];
        $source = new WpdbPostSource($db);
        $posts = $source->encodedPosts(['post', 'news'], ['publish'], [7, 8], 3, 50);

        self::assertCount(1, $posts, 'Rows are checked again in PHP.');
        self::assertSame(7, $posts[0]->id);
        self::assertCount(1, $db->prepared);
        [$sql, $args] = $db->prepared[0];
        self::assertStringContainsString('FROM %i WHERE ID > %d AND post_name REGEXP %s', $sql);
        self::assertStringContainsString('post_type IN (%s, %s)', $sql);
        self::assertStringContainsString('ID IN (%d, %d)', $sql);
        self::assertStringEndsWith('ORDER BY ID ASC LIMIT %d', $sql);
        self::assertSame(['wp_posts', 3, Encoding::HIGH_BYTE_SQL, 'post', 'news', 'publish', 7, 8, 50], $args);

        $db->count = 12;
        self::assertSame(12, $source->countEncoded(['post'], ['publish']));
        self::assertSame([], $source->encodedPosts([], ['publish'], null, 0, 50));
    }

    public function testEncodingHelpers(): void
    {
        self::assertTrue(Encoding::hasEncodedHighByte('abc-%E0%a4'));
        self::assertFalse(Encoding::hasEncodedHighByte('100%-sure-%20'));
        self::assertTrue(Encoding::isLikelyCut(str_repeat('%e0', 64)));
        self::assertFalse(Encoding::isLikelyCut(str_repeat('%e0', 60)));
    }
}
