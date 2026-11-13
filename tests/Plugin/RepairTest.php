<?php

declare(strict_types=1);

namespace IndianOpinion\AsciiSlugs\Tests\Plugin;

use IndianOpinion\AsciiSlugs\WordPress\Cli\Command;
use IndianOpinion\AsciiSlugs\WordPress\Plugin;
use IndianOpinion\AsciiSlugs\WordPress\RepairQuery;
use IndianOpinion\AsciiSlugs\WordPress\RepairRow;
use IndianOpinion\AsciiSlugs\WordPress\SlugRepair;

/**
 * PRD 4.9 and acceptance tests 51 to 58.
 */
final class RepairTest extends PluginTestCase
{
    private FakePostSource $source;

    private \WP_Post $cut;

    private \WP_Post $short;

    private \WP_Post $page;

    private \WP_Post $ascii;

    protected function setUp(): void
    {
        parent::setUp();
        $this->source = new FakePostSource();

        $longTitle = 'प्रधानमंत्री ने आज संसद में नई शिक्षा नीति पर विस्तार से चर्चा की';
        $this->cut = \WpStub::seedPost([
            'post_title' => $longTitle,
            'post_name' => self::coreSlug($longTitle),
        ]);
        $this->short = \WpStub::seedPost(['post_title' => 'भारत', 'post_name' => self::coreSlug('भारत')]);
        $this->page = \WpStub::seedPost(['post_title' => 'हमारे बारे में', 'post_name' => self::coreSlug('हमारे बारे में'), 'post_type' => 'page']);
        $this->ascii = \WpStub::seedPost(['post_title' => 'Hello', 'post_name' => 'hello']);
        \WpStub::seedPost(['post_title' => 'मसौदा', 'post_name' => self::coreSlug('मसौदा'), 'post_status' => 'draft']);
        \WpStub::$writes = 0;
    }

    private static function coreSlug(string $title): string
    {
        return sanitize_title_with_dashes($title, $title, 'save');
    }

    private function command(): Command
    {
        return new Command($this->plugin, $this->source);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function lastItems(): array
    {
        $items = \WP_CLI::messages('items');
        $decoded = json_decode((string) end($items), true);
        self::assertIsArray($decoded);

        return $decoded['items'];
    }

    public function testActivationDeactivationAndUninstallChangeNoSlug(): void
    {
        $before = array_map(static fn (\WP_Post $p): string => $p->post_name, \WpStub::$posts);
        $this->plugin->register();
        Plugin::boot();
        if (!defined('WP_UNINSTALL_PLUGIN')) {
            define('WP_UNINSTALL_PLUGIN', 'ascii-slugs/ascii-slugs.php');
        }
        require dirname(__DIR__, 2) . '/uninstall.php';

        self::assertSame($before, array_map(static fn (\WP_Post $p): string => $p->post_name, \WpStub::$posts));
        self::assertSame(0, \WpStub::$writes);
    }

    public function testAuditListsOnlyEncodedSlugsAndWritesNothing(): void
    {
        $rows = (new SlugRepair($this->plugin, $this->source))->audit(new RepairQuery(['post', 'page']));
        self::assertSame([$this->cut->ID, $this->short->ID, $this->page->ID], array_map(static fn (RepairRow $r): int => $r->id, $rows));
        self::assertTrue($rows[0]->likelyCut());
        self::assertGreaterThanOrEqual(192, $rows[0]->bytes());
        self::assertFalse($rows[1]->likelyCut());
        self::assertSame('bharat', $rows[1]->newSlug);
        self::assertSame(0, \WpStub::$writes);
        self::assertSame([], \WpStub::actionsNamed('ascii_slugs_generated'));
    }

    public function testAuditCommandFormats(): void
    {
        $this->command()->audit([], ['format' => 'count', 'post_type' => 'post,page']);
        $items = $this->lastItems();
        self::assertCount(3, $items);
        self::assertSame('yes', $items[0]['likely_cut']);
        self::assertSame(0, \WpStub::$writes);

        $this->command()->audit([], ['status' => 'draft']);
        self::assertCount(1, $this->lastItems());
    }

    public function testRepairWithoutYesWritesNothing(): void
    {
        $this->command()->repair([], []);
        self::assertSame(0, \WpStub::$writes);
        self::assertStringContainsString('Nothing was written', implode("\n", \WP_CLI::messages('log')));
        $results = array_column($this->lastItems(), 'result');
        self::assertSame(['dry-run', 'dry-run', 'skipped'], $results);
    }

    public function testRepairBuildsFromTheFullTitleAndKeepsTheOldUrlWorking(): void
    {
        $oldSlug = $this->cut->post_name;
        $this->command()->repair([], ['yes' => true, 'post_type' => 'post']);

        $repaired = get_post($this->cut->ID);
        self::assertNotNull($repaired);
        self::assertSame('pradhanamantri-ne-aj-sansad-men-nai-shiksha-niti-par-vistar-se-charcha-ki', $repaired->post_name);
        self::assertSame(2, \WpStub::$writes, 'Both posts are saved through the core update path.');
        self::assertSame([$oldSlug], \WpStub::$meta[$this->cut->ID]['_wp_old_slug']);
        self::assertSame($this->cut->ID, stub_old_slug_redirect($oldSlug));
        self::assertStringContainsString('Repaired 2, skipped 0, failed 0.', implode("\n", \WP_CLI::messages('success')));
    }

    public function testRepairSkipsPagesAndSaysWhy(): void
    {
        $report = (new SlugRepair($this->plugin, $this->source))->repair(new RepairQuery(['page']), true);
        self::assertCount(1, $report->rows);
        self::assertSame(RepairRow::SKIPPED, $report->rows[0]->outcome);
        self::assertStringContainsString('hierarchical', $report->rows[0]->message);
        self::assertSame(self::coreSlug('हमारे बारे में'), get_post($this->page->ID)?->post_name);
        self::assertSame(0, \WpStub::$writes);
    }

    public function testSecondRepairFindsNothing(): void
    {
        $repair = new SlugRepair($this->plugin, $this->source);
        $first = $repair->repair(new RepairQuery(['post']), true);
        self::assertSame(2, $first->count(RepairRow::REPAIRED));
        $second = $repair->repair(new RepairQuery(['post']), true);
        self::assertSame([], $second->rows);
    }

    public function testRepairedActionFires(): void
    {
        $old = $this->short->post_name;
        (new SlugRepair($this->plugin, $this->source))->repair(new RepairQuery(['post'], ['publish'], [$this->short->ID]), true);
        $fired = \WpStub::actionsNamed('ascii_slugs_repaired');
        self::assertSame([[$this->short->ID, $old, 'bharat']], array_map(static fn (array $a): array => $a[1], $fired));
    }

    public function testRepairMakesTheNewSlugUnique(): void
    {
        \WpStub::seedPost(['post_title' => 'Bharat', 'post_name' => 'bharat']);
        (new SlugRepair($this->plugin, $this->source))->repair(new RepairQuery(['post'], ['publish'], [$this->short->ID]), true);
        self::assertSame('bharat-2', get_post($this->short->ID)?->post_name);
        self::assertSame('bharat-2', \WpStub::actionsNamed('ascii_slugs_repaired')[0][1][2]);
    }

    public function testOneFailureIsReportedTheRestRepairedAndExitIsOne(): void
    {
        \WpStub::$failUpdates[$this->cut->ID] = 'Another plugin refused the update.';
        try {
            $this->command()->repair([], ['yes' => true, 'post_type' => 'post']);
            self::fail('repair must exit non-zero when a post fails');
        } catch (\WpCliHalt $halt) {
            self::assertSame(1, $halt->getCode());
            self::assertSame('Repaired 1, skipped 0, failed 1.', $halt->getMessage());
        }
        self::assertSame('bharat', get_post($this->short->ID)?->post_name);
        self::assertStringContainsString('Another plugin refused the update.', implode("\n", \WP_CLI::messages('warning')));
    }

    public function testLimitAndBatchWalkThePosts(): void
    {
        for ($i = 0; $i < 7; $i++) {
            \WpStub::seedPost(['post_title' => 'खबर ' . $i, 'post_name' => self::coreSlug('खबर ' . $i)]);
        }
        $rows = (new SlugRepair($this->plugin, $this->source))->audit(new RepairQuery(['post'], ['publish'], null, 5, 2));
        self::assertCount(5, $rows);
        self::assertSame(3, $this->source->queries);
        self::assertGreaterThanOrEqual(2, \WpStub::$cacheFlushes);
    }

    public function testBadArgumentsAreRejected(): void
    {
        foreach ([['limit' => '0'], ['batch' => 'x'], ['ids' => '1,a'], ['post_type' => 'Bad Type'], ['format' => 'xml']] as $assoc) {
            try {
                $this->command()->repair([], $assoc);
                self::fail('Accepted ' . json_encode($assoc));
            } catch (\WpCliHalt $halt) {
                self::assertSame(1, $halt->getCode());
            }
        }
        self::assertSame(0, \WpStub::$writes);
    }
}
