<?php

declare(strict_types=1);

namespace IndianOpinion\AsciiSlugs\Tests\Unit;

use IndianOpinion\AsciiSlugs\FixedClock;
use IndianOpinion\AsciiSlugs\SlugOptions;
use IndianOpinion\AsciiSlugs\Slugger;
use IndianOpinion\AsciiSlugs\SlugResult;
use IndianOpinion\AsciiSlugs\Transliterator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * PRD 4.8 and acceptance tests 48 to 50, plus the headline fixture that
 * backs the 0.5 coverage default.
 */
final class MixedScriptTest extends TestCase
{
    private static function slugger(): Slugger
    {
        return new Slugger(new SlugOptions(intlMode: 'never'), FixedClock::at('2026-11-12'));
    }

    public function testRunsKeepTheirOwnTables(): void
    {
        self::assertSame('isro-ka-chandrayan-3-mishan', self::slugger()->slug('ISRO का चंद्रयान-3 मिशन')->slug);
        self::assertSame('2026-chunav', self::slugger()->slug('२०२६ चुनाव')->slug);
    }

    public function testHindiTitleWithABengaliName(): void
    {
        $transliterator = new Transliterator('never');
        $result = (new Slugger(new SlugOptions(intlMode: 'never'), FixedClock::at('2026-11-12'), $transliterator))
            ->slug('हिंदी लेख में রবীন্দ্রনাথ का उल्लेख');
        self::assertSame('hindi-lekh-men-rabindranath-ka-ullekh', $result->slug);
        self::assertSame(['devanagari', 'bengali'], $result->scripts);
        self::assertSame(['devanagari', 'bengali'], $transliterator->loadedTables());
    }

    public function testCoverageDecidesBetweenSlugAndHash(): void
    {
        $thai = 'ข่าวด่วนวันนี้ตลาดหุ้นปิดบวกแรง';
        $low = self::slugger()->slug('भारत ' . $thai);
        self::assertSame(SlugResult::METHOD_HASHED, $low->method);
        self::assertLessThan(0.5, $low->coverage);

        $high = self::slugger()->slug('प्रधानमंत्री ने आज संसद में चर्चा की ข่าว');
        self::assertSame(SlugResult::METHOD_TRANSLITERATED, $high->method);
        self::assertGreaterThan(0.5, $high->coverage);
        self::assertSame('pradhanamantri-ne-aj-sansad-men-charcha-ki', $high->slug);
    }

    public function testStrayCombiningMarksAreDroppedAndLowerCoverage(): void
    {
        $clean = (new Transliterator('never'))->transliterate('भारत');
        // A Devanagari vowel sign after a Latin letter, and a Thai mark after Devanagari.
        $dirty = (new Transliterator('never'))->transliterate("भारत\u{0E48}");
        self::assertSame(1.0, $clean->coverage());
        self::assertSame('bharat', Slugger::clean($dirty->text));
        self::assertLessThan(1.0, $dirty->coverage());

        $stray = (new Transliterator('never'))->transliterate("\u{093F}abc");
        self::assertSame('abc', trim($stray->text));
        self::assertSame(3, $stray->coveredLetters);
        self::assertSame(4, $stray->letters);
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function headlines(): iterable
    {
        $fixture = require dirname(__DIR__) . '/fixtures/mixed-headlines.php';
        foreach ($fixture as $i => [$title, $expected]) {
            yield sprintf('#%02d', $i + 1) => [$title, $expected];
        }
    }

    #[DataProvider('headlines')]
    public function testHeadlineFixture(string $title, string $expected): void
    {
        $result = self::slugger()->slug($title);
        if ($expected === 'hashed') {
            self::assertSame(SlugResult::METHOD_HASHED, $result->method);
            self::assertLessThan(0.5, $result->coverage);
        } else {
            self::assertSame($expected, $result->slug);
            self::assertMatchesRegularExpression('/^[a-z0-9]+(-[a-z0-9]+)*$/', $result->slug);
        }
    }

    public function testHeadlineFixtureIsLargeAndMostlyReadable(): void
    {
        $fixture = require dirname(__DIR__) . '/fixtures/mixed-headlines.php';
        self::assertIsArray($fixture);
        self::assertGreaterThanOrEqual(40, count($fixture));
        $hashed = count(array_filter($fixture, static fn (array $row): bool => $row[1] === 'hashed'));
        self::assertSame(6, $hashed, 'Only the headlines that are mostly in scripts with no table should hash.');
    }
}
