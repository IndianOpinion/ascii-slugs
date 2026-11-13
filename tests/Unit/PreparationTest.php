<?php

declare(strict_types=1);

namespace IndianOpinion\AsciiSlugs\Tests\Unit;

use IndianOpinion\AsciiSlugs\Exception\InvalidInputException;
use IndianOpinion\AsciiSlugs\FixedClock;
use IndianOpinion\AsciiSlugs\SlugOptions;
use IndianOpinion\AsciiSlugs\Slugger;
use IndianOpinion\AsciiSlugs\Transliterator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * PRD 4.1 and acceptance tests 1 to 7.
 */
final class PreparationTest extends TestCase
{
    private Slugger $slugger;

    protected function setUp(): void
    {
        $this->slugger = new Slugger(null, FixedClock::at('2026-11-12'));
    }

    public function testInvalidUtf8Throws(): void
    {
        $this->expectException(InvalidInputException::class);
        $this->slugger->slug("abc \xE0\xA4 def");
    }

    public function testInvalidUtf8ThrowsFromFallbackAndTransliterator(): void
    {
        try {
            $this->slugger->fallback("\xFF");
            self::fail('fallback() accepted invalid UTF-8');
        } catch (InvalidInputException) {
        }
        $this->expectException(InvalidInputException::class);
        (new Transliterator())->transliterate("\xC3");
    }

    public function testEntitiesAreDecodedRepeatedly(): void
    {
        self::assertSame('modis-visit', $this->slugger->slug('Modi&amp;#8217;s visit')->slug);
        self::assertSame('modis-visit', $this->slugger->slug('Modi&amp;amp;#8217;s visit')->slug);
    }

    public function testTagsAreStrippedAndTheirTextKept(): void
    {
        self::assertSame('bharat', $this->slugger->slug('<b>भारत</b>')->slug);
        self::assertSame('bharat', $this->slugger->slug('&lt;em&gt;भारत&lt;/em&gt;')->slug);
    }

    public function testJoinersInsideConjunctsChangeNothing(): void
    {
        $plain = 'राष्ट्रपति ने क्षेत्र का दौरा किया';
        $joined = "राष्\u{200D}ट्रपति ने क्\u{200C}षेत्र का\u{200B} दौरा किया";
        self::assertSame($this->slugger->slug($plain)->slug, $this->slugger->slug($joined)->slug);
    }

    public function testOtherInvisibleCharactersLeaveNoSeparator(): void
    {
        $title = "भा\u{2060}र\u{00AD}त\u{FEFF} \u{200E}پا\u{0640}کستان\u{200F}";
        self::assertSame('bharat-pakstan', $this->slugger->slug($title)->slug);
    }

    #[DataProvider('apostrophes')]
    public function testApostrophesInsideWordsLeaveNoHyphen(string $title): void
    {
        self::assertSame('modis-visit', $this->slugger->slug($title)->slug);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function apostrophes(): iterable
    {
        yield 'straight' => ["Modi's visit"];
        yield 'curly' => ["Modi\u{2019}s visit"];
        yield 'modifier letter' => ["Modi\u{02BC}s visit"];
        yield 'left single' => ["Modi\u{2018}s visit"];
        yield 'double' => ['Modi"s visit'];
    }

    public function testQuotesAroundWordsBecomeSeparators(): void
    {
        self::assertSame('man-ki-bat', $this->slugger->slug("\u{201C}मन\u{201D} की बात")->slug);
    }

    public function testLongDevanagariTitleIsCappedAndFast(): void
    {
        $word = 'समाचार ';
        $title = str_repeat($word, intdiv(5000, 7));
        $start = hrtime(true);
        $result = $this->slugger->slug($title);
        $elapsedMs = (hrtime(true) - $start) / 1e6;

        self::assertLessThanOrEqual(80, strlen($result->slug));
        self::assertSame('transliterated', $result->method);
        self::assertLessThan(250.0, $elapsedMs, 'A 5,000 code point title took too long.');
    }

    public function testHashCoversTheWholeTitleNotOnlyTheCap(): void
    {
        $thai = str_repeat('ข่าวด่วน', 625); // 5,000 code points
        self::assertSame(5000, preg_match_all('/./u', $thai));
        $first1000 = implode('', array_slice(preg_split('//u', $thai, -1, PREG_SPLIT_NO_EMPTY) ?: [], 0, 1000));

        $never = new Slugger(new SlugOptions(intlMode: 'never'), FixedClock::at('2026-11-12'));
        $whole = $never->slug($thai);
        self::assertSame('hashed', $whole->method);
        self::assertNotSame($never->fallback($first1000), $whole->slug);
        self::assertSame($never->fallback($thai), $whole->slug);
    }

    public function testPrecomposedAndDecomposedNuktaGiveTheSameSlug(): void
    {
        $precomposed = "\u{095B}िंदगी";
        $decomposed = "\u{091C}\u{093C}िंदगी";
        self::assertSame('zindagi', $this->slugger->slug($precomposed)->slug);
        self::assertSame('zindagi', $this->slugger->slug($decomposed)->slug);
    }

    public function testPrecomposedAndDecomposedLatinGiveTheSameSlug(): void
    {
        self::assertSame('cafe-ole', $this->slugger->slug("caf\u{00E9} ol\u{00E9}")->slug);
        self::assertSame('cafe-ole', $this->slugger->slug("cafe\u{0301} ole\u{0301}")->slug);
    }

    public function testPrecomposedAndDecomposedCyrillicGiveTheSameSlug(): void
    {
        self::assertSame('moy-kray', $this->slugger->slug("мо\u{0439} кра\u{0439}")->slug);
        self::assertSame('moy-kray', $this->slugger->slug("мо\u{0438}\u{0306} кра\u{0438}\u{0306}")->slug);
    }

    public function testPrecomposedAndDecomposedSplitVowelsGiveTheSameSlug(): void
    {
        // Tamil o-sign as one code point, and as e-sign + aa-sign.
        self::assertSame(
            $this->slugger->slug("\u{0B95}\u{0BCA}\u{0B9F}\u{0BC8}")->slug,
            $this->slugger->slug("\u{0B95}\u{0BC6}\u{0BBE}\u{0B9F}\u{0BC8}")->slug
        );
        // Bengali o-sign.
        self::assertSame(
            $this->slugger->slug("\u{0995}\u{09CB}\u{09A8}")->slug,
            $this->slugger->slug("\u{0995}\u{09C7}\u{09BE}\u{09A8}")->slug
        );
    }
}
