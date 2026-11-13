<?php

declare(strict_types=1);

namespace IndianOpinion\AsciiSlugs\Tests\Unit;

use DateTimeImmutable;
use DateTimeZone;
use IndianOpinion\AsciiSlugs\Exception\InvalidOptionException;
use IndianOpinion\AsciiSlugs\Exception\SlugCollisionException;
use IndianOpinion\AsciiSlugs\FixedClock;
use IndianOpinion\AsciiSlugs\SlugOptions;
use IndianOpinion\AsciiSlugs\Slugger;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * PRD 4.4, 4.5 and acceptance tests 29 to 34.
 */
final class FallbackAndUniqueTest extends TestCase
{
    private const THAI = 'ข่าวด่วนวันนี้';

    private static function slugger(?SlugOptions $options = null): Slugger
    {
        return new Slugger($options ?? new SlugOptions(intlMode: 'never'), FixedClock::at('2026-11-12'));
    }

    public function testFallbackFormatAndLength(): void
    {
        $slug = self::slugger()->fallback(self::THAI);
        self::assertMatchesRegularExpression('/^post-20261112-[0-9a-f]{8}$/', $slug);
        self::assertSame(22, strlen($slug));
        self::assertSame('post-20261112-' . substr(hash('sha256', self::THAI), 0, 8), $slug);

        self::assertSame(13, strlen(self::slugger(new SlugOptions(includeDate: false))->fallback(self::THAI)));
        self::assertSame(26, strlen(self::slugger(new SlugOptions(hashLength: 12))->fallback(self::THAI)));
        $custom = self::slugger(new SlugOptions(fallbackPrefix: 'news', hashLength: 16, includeDate: false));
        self::assertMatchesRegularExpression('/^news-[0-9a-f]{16}$/', $custom->fallback(self::THAI));
    }

    public function testFallbackUsesAGivenDateInUtc(): void
    {
        $date = new DateTimeImmutable('2026-03-01 02:00:00', new DateTimeZone('Asia/Kolkata'));
        self::assertStringStartsWith('post-20260228-', self::slugger()->fallback(self::THAI, $date));
        self::assertStringStartsWith('post-20260228-', self::slugger()->slug(self::THAI, $date)->slug);
    }

    public function testFallbackIsOverThePreparedTitle(): void
    {
        self::assertSame(self::slugger()->fallback('<b>' . self::THAI . '</b>'), self::slugger()->fallback(self::THAI));
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function badPrefixes(): iterable
    {
        yield 'upper case' => ['Post'];
        yield 'leading digit' => ['1post'];
        yield 'hyphen' => ['post-x'];
        yield 'too long' => [str_repeat('p', 21)];
        yield 'empty' => [''];
        yield 'trailing newline' => ["post\n"];
    }

    #[DataProvider('badPrefixes')]
    public function testBadPrefixesAreRejected(string $prefix): void
    {
        $this->expectException(InvalidOptionException::class);
        new SlugOptions(fallbackPrefix: $prefix);
    }

    public function testTwentyCharacterPrefixIsAllowed(): void
    {
        $options = new SlugOptions(fallbackPrefix: str_repeat('p', 20), hashLength: 16);
        self::assertSame(20 + 1 + 8 + 1 + 16, strlen(self::slugger($options)->fallback(self::THAI)));
    }

    public function testSameTitleSameDaySameFallback(): void
    {
        self::assertSame(self::slugger()->fallback(self::THAI), self::slugger()->fallback(self::THAI));
        self::assertNotSame(self::slugger()->fallback(self::THAI), self::slugger()->fallback(self::THAI . 'ก'));
        $otherDay = new Slugger(new SlugOptions(), FixedClock::at('2026-11-13'));
        self::assertNotSame(self::slugger()->fallback(self::THAI), $otherDay->fallback(self::THAI));
    }

    public function testZeroWidthOnlyTitleStillHashes(): void
    {
        $result = self::slugger()->slug("\u{200B}\u{200C}\u{200D}\u{FEFF}");
        self::assertTrue($result->isHashed());
        self::assertSame('post-20261112-' . substr(hash('sha256', ''), 0, 8), $result->slug);
    }

    public function testFallbackForBytesAcceptsInvalidUtf8(): void
    {
        $slug = self::slugger()->fallbackForBytes("\xE0\xA4");
        self::assertSame('post-20261112-' . substr(hash('sha256', "\xE0\xA4"), 0, 8), $slug);
    }

    public function testUniqueAddsSuffixes(): void
    {
        $slugger = self::slugger();
        self::assertSame('slug', $slugger->unique('slug', static fn (): bool => false));
        self::assertSame('slug-2', $slugger->unique('slug', static fn (string $s): bool => $s === 'slug'));
        $taken = ['slug', 'slug-2', 'slug-3'];
        self::assertSame('slug-4', $slugger->unique('slug', static fn (string $s): bool => in_array($s, $taken, true)));
    }

    public function testUniqueShortensTheBaseToFitMaxLength(): void
    {
        $slugger = self::slugger(new SlugOptions(maxLength: 20, minCutPosition: 5));
        $slug = 'alpha-bravo-charlie1';
        self::assertSame(20, strlen($slug));
        $unique = $slugger->unique($slug, static fn (string $s): bool => !str_ends_with($s, '-10'));
        self::assertSame('alpha-bravo-10', $unique);
        self::assertLessThanOrEqual(20, strlen($unique));

        $hard = self::slugger(new SlugOptions(maxLength: 20))->unique(str_repeat('x', 20), static fn (string $s): bool => !str_ends_with($s, '-10'));
        self::assertSame(str_repeat('x', 17) . '-10', $hard);
    }

    public function testUniqueGivesUpAfterMaxTries(): void
    {
        $tries = 0;
        $this->expectException(SlugCollisionException::class);
        try {
            self::slugger()->unique('slug', static function () use (&$tries): bool {
                $tries++;

                return true;
            }, 5);
        } finally {
            self::assertSame(5, $tries);
        }
    }

    public function testMaxLengthIsCappedAt190(): void
    {
        new SlugOptions(maxLength: 190);
        $this->expectException(InvalidOptionException::class);
        new SlugOptions(maxLength: 191);
    }
}
