<?php

declare(strict_types=1);

namespace IndianOpinion\AsciiSlugs\Tests\Plugin;

/**
 * PRD 4.10, section 8 and acceptance tests 59 and 61.
 */
final class FailureModesTest extends PluginTestCase
{
    public function testCoveredScriptsWorkWithIntlOff(): void
    {
        add_filter('ascii_slugs_options', static fn (array $o): array => ['intlMode' => 'never'] + $o);
        self::assertSame('namaste', sanitize_title('नमस्ते'));
        self::assertSame('never', $this->plugin->options()->intlMode);
    }

    public function testNonAsciiFromTheSlugFilterIsCleanedAgain(): void
    {
        add_filter('ascii_slugs_slug', static fn (): string => "  Br\u{00FC}cke  NEWS!! ");
        $slug = sanitize_title('भारत');
        self::assertMatchesRegularExpression('/^[a-z0-9]+(-[a-z0-9]+)*$/', $slug);
        self::assertSame('br-cke-news', $slug);
    }

    public function testEmptyValueFromTheSlugFilterGivesTheHash(): void
    {
        add_filter('ascii_slugs_slug', static fn (): string => '!!!');
        self::assertMatchesRegularExpression('/^post-20261112-[0-9a-f]{8}$/', sanitize_title('भारत'));
    }

    public function testTooLongValueFromTheSlugFilterIsCut(): void
    {
        add_filter('ascii_slugs_slug', static fn (): string => str_repeat('word-', 60));
        self::assertLessThanOrEqual(80, strlen(sanitize_title('भारत')));
    }

    public function testInvalidOptionsFallBackToDefaultsWithOneLogLine(): void
    {
        $this->enableLogging();
        add_filter('ascii_slugs_options', static fn (array $o): array => ['maxLength' => 500] + $o);
        self::assertSame(80, $this->plugin->options()->maxLength);
        self::assertCount(1, $this->logLines);
        self::assertStringContainsString('InvalidOptionException', $this->logLines[0]);
        self::assertSame('bharat', sanitize_title('भारत'));
    }

    public function testWrongTypesFromFiltersAreIgnored(): void
    {
        $this->enableLogging();
        add_filter('ascii_slugs_options', static fn (): string => 'nonsense');
        add_filter('ascii_slugs_slug', static fn (): int => 42);
        add_filter('ascii_slugs_should_handle', static fn (): string => 'no');
        add_filter('ascii_slugs_enabled', static fn (): string => 'no');
        add_filter('ascii_slugs_post_types', static fn (): string => 'post');

        self::assertSame('bharat', sanitize_title('भारत'));
        self::assertContains('post', $this->plugin->postTypes());
        self::assertTrue($this->plugin->isEnabled());
        $joined = implode("\n", $this->logLines);
        $filters = [
            'ascii_slugs_options', 'ascii_slugs_slug', 'ascii_slugs_should_handle', 'ascii_slugs_enabled', 'ascii_slugs_post_types',
        ];
        foreach ($filters as $filter) {
            self::assertStringContainsString($filter, $joined);
        }
    }

    public function testLoggingIsOffByDefault(): void
    {
        sanitize_title('भारत');
        self::assertSame([], $this->logLines);
    }

    public function testLogLinesNeverHoldTheRawTitle(): void
    {
        $this->enableLogging();
        $title = 'गोपनीय खबर: मंत्री का इस्तीफा';
        $slug = sanitize_title($title);
        sanitize_title("\xE0\xA4\xAD\xE0\xA4 गोपनीय");

        self::assertCount(2, $this->logLines);
        self::assertSame('ascii-slugs: method=transliterated coverage=1.00 slug=' . substr($slug, 0, 40), $this->logLines[0]);
        self::assertStringStartsWith('ascii-slugs: error=IndianOpinion\AsciiSlugs\Exception\InvalidInputException', $this->logLines[1]);
        foreach ($this->logLines as $line) {
            self::assertStringNotContainsString('गोपनीय', $line);
            self::assertStringNotContainsString('मंत्री', $line);
            self::assertMatchesRegularExpression('/^[\x20-\x7E]+$/', $line);
        }
    }

    public function testAsciiTitlesAreNotLogged(): void
    {
        $this->enableLogging();
        sanitize_title('Hello world');
        self::assertSame([], $this->logLines);
    }
}
