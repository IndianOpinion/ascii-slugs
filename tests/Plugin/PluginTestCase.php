<?php

declare(strict_types=1);

namespace IndianOpinion\AsciiSlugs\Tests\Plugin;

use IndianOpinion\AsciiSlugs\FixedClock;
use IndianOpinion\AsciiSlugs\SlugOptions;
use IndianOpinion\AsciiSlugs\Slugger;
use IndianOpinion\AsciiSlugs\WordPress\Logger;
use IndianOpinion\AsciiSlugs\WordPress\Plugin;
use PHPUnit\Framework\TestCase;

abstract class PluginTestCase extends TestCase
{
    protected Plugin $plugin;

    /** Number of times the plugin asked for a slugger. */
    protected int $sluggerBuilds = 0;

    /** @var list<string> */
    protected array $logLines = [];

    public static function setUpBeforeClass(): void
    {
        require_once dirname(__DIR__) . '/stubs/wordpress.php';
        require_once dirname(__DIR__) . '/stubs/wp-cli.php';
    }

    protected function setUp(): void
    {
        \WpStub::reset();
        \WP_CLI::reset();
        $this->sluggerBuilds = 0;
        $this->logLines = [];
        $this->plugin = $this->makePlugin();
        $this->plugin->register();
        // Core's own sanitiser at priority 10.
        add_filter('sanitize_title', 'sanitize_title_with_dashes', 10, 3);
    }

    protected function makePlugin(): Plugin
    {
        $factory = function (SlugOptions $options): Slugger {
            $this->sluggerBuilds++;

            return new Slugger($options, FixedClock::at('2026-11-12'));
        };
        $logger = new Logger(function (string $line): void {
            $this->logLines[] = $line;
        });

        return new Plugin($factory, $logger);
    }

    protected function enableLogging(): void
    {
        add_filter('ascii_slugs_log', static fn (): bool => true);
    }

    /**
     * Saves a new post through the stub insert path and returns it.
     *
     * @param array<string, mixed> $fields
     */
    protected function insertPost(array $fields): \WP_Post
    {
        $id = wp_insert_post($fields + ['post_type' => 'post', 'post_status' => 'publish']);
        self::assertIsInt($id);
        $post = get_post($id);
        self::assertNotNull($post);

        return $post;
    }
}
