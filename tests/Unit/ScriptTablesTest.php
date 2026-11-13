<?php

declare(strict_types=1);

namespace IndianOpinion\AsciiSlugs\Tests\Unit;

use IndianOpinion\AsciiSlugs\FixedClock;
use IndianOpinion\AsciiSlugs\Internal\BuiltinTables;
use IndianOpinion\AsciiSlugs\SlugOptions;
use IndianOpinion\AsciiSlugs\Slugger;
use IndianOpinion\AsciiSlugs\Transliterator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * PRD 4.2 and acceptance tests 8 to 21.
 */
final class ScriptTablesTest extends TestCase
{
    private function slug(string $title, ?SlugOptions $options = null): string
    {
        return (new Slugger($options ?? new SlugOptions(intlMode: 'never'), FixedClock::at('2026-11-12')))->slug($title)->slug;
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function words(): iterable
    {
        yield 'devanagari namaste' => ['नमस्ते', 'namaste'];
        yield 'devanagari bharat' => ['भारत', 'bharat'];
        yield 'devanagari sampadak' => ['संपादक', 'sampadak'];
        yield 'devanagari conjunct st' => ['अस्त', 'ast'];
        yield 'devanagari gy' => ['ज्ञान', 'gyan'];
        yield 'devanagari sounded final conjunct' => ['मित्र', 'mitra'];
        yield 'devanagari chandrabindu' => ['हँसी', 'hansi'];
        yield 'devanagari visarga' => ['दुःख', 'duhkh'];
        yield 'devanagari vocalic r' => ['कृषि', 'krishi'];
        yield 'devanagari nukta f' => ['फ़िल्म', 'film'];
        yield 'devanagari om' => ['ॐ शांति', 'om-shanti'];
        yield 'devanagari danda' => ['सत्य। अहिंसा॥ धर्म', 'satya-ahinsa-dharm'];
        yield 'devanagari anusvara before b' => ['अंबर', 'ambar'];
        yield 'bengali' => ['বাংলা', 'bangla'];
        yield 'bengali khanda ta' => ['উৎসব', 'utsab'];
        yield 'bengali nukta' => ['বাড়ি', 'bari'];
        yield 'assamese ra wa' => ['অসমৰ গুৱাহাটী', 'asamar-guwahati'];
        yield 'gurmukhi' => ['ਪੰਜਾਬ', 'panjab'];
        yield 'gurmukhi addak' => ['ਪੱਕਾ', 'pakka'];
        yield 'gujarati' => ['ગુજરાત', 'gujarat'];
        yield 'odia' => ['ଓଡ଼ିଆ', 'oria'];
        yield 'tamil' => ['தமிழ்', 'tamizh'];
        yield 'tamil grantha' => ['ஜனஷஸஹ', 'janashasaha'];
        yield 'tamil aytham' => ['அஃது', 'ahtu'];
        yield 'telugu' => ['తెలుగు', 'telugu'];
        yield 'telugu final anusvara' => ['విజయం', 'vijayam'];
        yield 'kannada' => ['ಕನ್ನಡ', 'kannada'];
        yield 'kannada bengaluru' => ['ಬೆಂಗಳೂರು', 'bengaluru'];
        yield 'malayalam' => ['മലയാളം', 'malayalam'];
        yield 'malayalam chillu' => ['അവൻ കേരളർ', 'avan-keralar'];
        yield 'malayalam zh' => ['കോഴിക്കോട്', 'kozhikkot'];
        yield 'urdu pakistan' => ['پاکستان', 'pakstan'];
        yield 'urdu lahore' => ['لاہور', 'lahor'];
        yield 'urdu exception' => ['اردو', 'urdu'];
        yield 'urdu y at start' => ['یار', 'yar'];
        yield 'urdu w at start' => ['وطن', 'wtn'];
        yield 'urdu short vowels when written' => ['کِتاب', 'kitab'];
        yield 'urdu shadda' => ['مُحَمَّد', 'muhammad'];
        yield 'cyrillic' => ['Москва', 'moskva'];
        yield 'cyrillic signs dropped' => ['Объявление', 'obyavlenie'];
        yield 'greek' => ['Αθήνα', 'athina'];
        yield 'greek digraphs' => ['Ψυχή Χάος', 'psychi-chaos'];
        yield 'latin' => ['Straße Ørsted', 'strasse-orsted'];
        yield 'latin expansions' => ['Æsir Œuvre Łódź Þór', 'aesir-oeuvre-lodz-thor'];
    }

    #[DataProvider('words')]
    public function testTableOutput(string $title, string $expected): void
    {
        self::assertSame($expected, $this->slug($title));
    }

    public function testDoChashmiHeAspirates(): void
    {
        self::assertStringStartsWith('bh', $this->slug('بھارت'));
        self::assertStringStartsWith('kh', $this->slug('کھیل'));
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function digitSets(): iterable
    {
        foreach (
            [
            'devanagari' => 0x0966, 'bengali' => 0x09E6, 'gurmukhi' => 0x0A66, 'gujarati' => 0x0AE6,
            'odia' => 0x0B66, 'tamil' => 0x0BE6, 'telugu' => 0x0C66, 'kannada' => 0x0CE6,
            'malayalam' => 0x0D66, 'arabic-indic' => 0x0660, 'extended arabic-indic' => 0x06F0,
            ] as $name => $zero
        ) {
            $digits = '';
            for ($d = 0; $d < 10; $d++) {
                $digits .= html_entity_decode('&#' . ($zero + $d) . ';', ENT_QUOTES, 'UTF-8');
            }
            yield $name => [$digits];
        }
    }

    #[DataProvider('digitSets')]
    public function testNativeDigits(string $digits): void
    {
        self::assertSame('0123456789', (new Transliterator('never'))->transliterate($digits)->text);
    }

    public function testTamilNumberSigns(): void
    {
        self::assertSame('10 100 1000', (new Transliterator('never'))->transliterate('௰ ௱ ௲')->text);
    }

    public function testFinalInherentVowelSwitch(): void
    {
        $keep = new SlugOptions(intlMode: 'never', dropFinalInherentVowel: false);
        self::assertSame('bharat', $this->slug('भारत'));
        self::assertSame('bharata', $this->slug('भारत', $keep));
        self::assertSame('panjaba', $this->slug('ਪੰਜਾਬ', $keep));
        self::assertSame('kamal', $this->slug('कमल'));
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function scriptsThatKeepTheFinalVowel(): iterable
    {
        yield 'tamil' => ['கமல'];
        yield 'telugu' => ['కమల'];
        yield 'kannada' => ['ಕಮಲ'];
        yield 'malayalam' => ['കമല'];
        yield 'odia' => ['କମଲ'];
    }

    #[DataProvider('scriptsThatKeepTheFinalVowel')]
    public function testSomeScriptsKeepTheFinalVowelEitherWay(string $word): void
    {
        self::assertSame('kamala', $this->slug($word));
        self::assertSame('kamala', $this->slug($word, new SlugOptions(intlMode: 'never', dropFinalInherentVowel: false)));
    }

    public function testSingleConsonantWordKeepsItsVowel(): void
    {
        self::assertSame('bharat-na-pakstan', $this->slug('भारत न پاکستان'));
    }

    public function testOnlyTheNeededTableIsLoaded(): void
    {
        $transliterator = new Transliterator('never');
        self::assertSame([], $transliterator->loadedTables());
        $transliterator->transliterate('नमस्ते भारत');
        self::assertSame(['devanagari'], $transliterator->loadedTables());
        $transliterator->transliterate('தமிழ்');
        self::assertSame(['devanagari', 'tamil'], $transliterator->loadedTables());
    }

    public function testPlainAsciiLoadsNoTable(): void
    {
        $transliterator = new Transliterator('never');
        $transliterator->transliterate('Hello world 2026');
        self::assertSame([], $transliterator->loadedTables());
    }

    public function testIndexRangesMatchTheTableFiles(): void
    {
        foreach (BuiltinTables::RANGES as $name => $ranges) {
            $definition = require BuiltinTables::directory() . '/' . $name . '.php';
            self::assertIsArray($definition);
            self::assertSame($name, $definition['name']);
            self::assertSame($ranges, $definition['ranges'], $name);
        }
    }

    public function testEveryTableOutputIsLowerAsciiOrDigits(): void
    {
        foreach (BuiltinTables::names() as $name) {
            $definition = require BuiltinTables::directory() . '/' . $name . '.php';
            self::assertIsArray($definition);
            foreach ($definition['classes'] as $entries) {
                foreach ($entries as $ascii) {
                    self::assertMatchesRegularExpression('/^[a-z0-9 ]*$/', $ascii, $name);
                }
            }
        }
    }

    public function testUrduExceptionListStaysSmall(): void
    {
        $words = require BuiltinTables::directory() . '/urdu-words.php';
        self::assertIsArray($words);
        self::assertLessThanOrEqual(20, count($words));
    }

    public function testBuiltinScriptsAreListed(): void
    {
        self::assertSame(
            [
                'devanagari', 'bengali', 'gurmukhi', 'gujarati', 'odia', 'tamil', 'telugu', 'kannada', 'malayalam',
                'arabic', 'cyrillic', 'greek', 'latin',
            ],
            Transliterator::builtinScripts()
        );
    }
}
