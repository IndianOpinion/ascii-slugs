<?php

declare(strict_types=1);

namespace IndianOpinion\AsciiSlugs\Tests\Unit;

use IndianOpinion\AsciiSlugs\FixedClock;
use IndianOpinion\AsciiSlugs\SlugOptions;
use IndianOpinion\AsciiSlugs\Slugger;
use IndianOpinion\AsciiSlugs\SlugResult;
use IndianOpinion\AsciiSlugs\Transliterator;
use PHPUnit\Framework\TestCase;

/**
 * PRD 4.3 and acceptance tests 22 to 28.
 */
final class SlugBuildingTest extends TestCase
{
    private const THAI = 'ข่าวด่วนวันนี้ตลาดหุ้นปิดบวก';

    private const COVERED_FIXTURES = [
        'नमस्ते भारत', 'বাংলা', 'ਪੰਜਾਬ', 'ગુજરાત', 'ଓଡ଼ିଆ', 'தமிழ்', 'తెలుగు', 'ಕನ್ನಡ', 'മലയാളം',
        'پاکستان', 'Москва', 'Αθήνα', 'Straße Ørsted', 'ISRO का चंद्रयान-3 मिशन',
    ];

    private static function slugger(string $intlMode = 'never', ?SlugOptions $options = null): Slugger
    {
        $options = ($options ?? new SlugOptions())->withIntlMode($intlMode);

        return new Slugger($options, FixedClock::at('2026-11-12'));
    }

    public function testUncoveredScriptWithoutIntlIsHashed(): void
    {
        $result = self::slugger('never')->slug(self::THAI);
        self::assertSame(SlugResult::METHOD_HASHED, $result->method);
        self::assertMatchesRegularExpression('/^post-20261112-[0-9a-f]{8}$/', $result->slug);
    }

    public function testUncoveredScriptWithIntlIsTransliterated(): void
    {
        if (!Transliterator::intlAvailable()) {
            self::markTestSkipped('intl is not installed.');
        }
        $result = self::slugger('auto')->slug(self::THAI);
        self::assertSame(SlugResult::METHOD_INTL, $result->method);
        self::assertMatchesRegularExpression('/^[a-z0-9]+(-[a-z0-9]+)*$/', $result->slug);
        self::assertSame(['thai'], $result->scripts);
    }

    public function testCoveredScriptsDoNotDependOnIntl(): void
    {
        foreach (self::COVERED_FIXTURES as $title) {
            $never = self::slugger('never')->slug($title);
            $auto = self::slugger('auto')->slug($title);
            self::assertSame($never->slug, $auto->slug, $title);
            self::assertSame(SlugResult::METHOD_TRANSLITERATED, $auto->method, $title);
        }
    }

    public function testPreferModeUsesIcuForCoveredScripts(): void
    {
        if (!Transliterator::intlAvailable()) {
            self::markTestSkipped('intl is not installed.');
        }
        $result = self::slugger('prefer')->slug('नमस्ते भारत');
        self::assertSame(SlugResult::METHOD_INTL, $result->method);
        self::assertMatchesRegularExpression('/^[a-z0-9-]+$/', $result->slug);
    }

    public function testOutputShape(): void
    {
        $slugger = self::slugger();
        foreach (['  --Hello,  World!!-- ', 'नमस्ते -- भारत ...', '(ISRO) / चंद्रयान'] as $title) {
            $slug = $slugger->slug($title)->slug;
            self::assertMatchesRegularExpression('/^[a-z0-9]+(-[a-z0-9]+)*$/', $slug, $title);
        }
    }

    public function testLongHindiTitleIsCutAtAWordBoundary(): void
    {
        $title = 'प्रधानमंत्री ने आज संसद में नई शिक्षा नीति पर विस्तार से चर्चा की और राज्यों से सहयोग की अपील की';
        $full = self::slugger('never', new SlugOptions(maxLength: 190))->slug($title)->slug;
        $cut = self::slugger()->slug($title)->slug;

        self::assertGreaterThan(80, strlen($full));
        self::assertLessThanOrEqual(80, strlen($cut));
        self::assertStringEndsNotWith('-', $cut);
        self::assertStringStartsWith($cut . '-', $full, 'The cut must fall on a word boundary.');
    }

    public function testTitleWithNoEarlyHyphenIsCutHard(): void
    {
        $title = str_repeat('a', 100) . ' word';
        $slug = self::slugger()->slug($title)->slug;
        self::assertSame(str_repeat('a', 80), $slug);
    }

    public function testCutBeforeMinCutPositionFallsBackToHardCut(): void
    {
        $title = 'ab ' . str_repeat('c', 100);
        $slug = self::slugger()->slug($title)->slug;
        self::assertSame(80, strlen($slug));
        self::assertStringStartsWith('ab-ccc', $slug);
    }

    public function testShortOrDigitOnlySlugsAreHashed(): void
    {
        $ek = self::slugger()->slug('एक');
        self::assertSame(SlugResult::METHOD_HASHED, $ek->method);
        $year = self::slugger()->slug('२०२६');
        self::assertSame(SlugResult::METHOD_HASHED, $year->method);
    }

    public function testMinAlnumIsConfigurable(): void
    {
        $result = self::slugger('never', new SlugOptions(minAlnum: 2))->slug('एक');
        self::assertSame('ek', $result->slug);
    }

    public function testMostlyUncoveredTitleIsHashedForCoverage(): void
    {
        $result = self::slugger('never')->slug('Breaking ' . self::THAI);
        self::assertSame(SlugResult::METHOD_HASHED, $result->method);
        self::assertLessThan(0.5, $result->coverage);
        self::assertSame(['latin', 'thai'], $result->scripts);
    }

    public function testResultReportsMethodCoverageAndScripts(): void
    {
        $ascii = self::slugger()->slug('Hello World');
        self::assertSame(['hello-world', 'ascii', 1.0, ['latin']], [$ascii->slug, $ascii->method, $ascii->coverage, $ascii->scripts]);

        $trans = self::slugger()->slug('ISRO का मिशन');
        self::assertSame(
            ['isro-ka-mishan', 'transliterated', 1.0, ['latin', 'devanagari']],
            [$trans->slug, $trans->method, $trans->coverage, $trans->scripts]
        );

        $hashed = self::slugger()->slug(self::THAI);
        self::assertSame(['hashed', 0.0, ['thai']], [$hashed->method, $hashed->coverage, $hashed->scripts]);
        self::assertTrue($hashed->isHashed());
    }

    public function testTransliterationResultDetails(): void
    {
        $result = (new Transliterator('never'))->transliterate('Hi ' . 'ข่าว');
        self::assertSame(['latin', 'thai'], $result->scripts);
        self::assertSame(['thai'], $result->uncoveredScripts);
        self::assertFalse($result->usedIntl);
        self::assertSame(2, $result->coveredLetters);
        self::assertSame(6, $result->letters);
        self::assertEqualsWithDelta(2 / 6, $result->coverage(), 0.0001);
    }

    public function testSameInputSameOutput(): void
    {
        $title = 'ISRO का चंद्रयान-3 मिशन';
        self::assertEquals(self::slugger()->slug($title), self::slugger()->slug($title));
    }
}
