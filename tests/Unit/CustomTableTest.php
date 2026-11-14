<?php

declare(strict_types=1);

namespace IndianOpinion\AsciiSlugs\Tests\Unit;

use IndianOpinion\AsciiSlugs\ArrayScriptTable;
use IndianOpinion\AsciiSlugs\Exception\InvalidOptionException;
use IndianOpinion\AsciiSlugs\FixedClock;
use IndianOpinion\AsciiSlugs\ScriptTable;
use IndianOpinion\AsciiSlugs\SlugOptions;
use IndianOpinion\AsciiSlugs\Slugger;
use IndianOpinion\AsciiSlugs\Transliterator;
use PHPUnit\Framework\TestCase;

/**
 * Transliterator::withTable() and the ScriptTable interface.
 */
final class CustomTableTest extends TestCase
{
    public function testAnExtraAlphabetTableCoversANewScript(): void
    {
        $thai = new ArrayScriptTable('thai-basic', 'alphabet', [[0x0E00, 0x0E7F]], [
            'letter' => ["\u{0E01}" => 'k', "\u{0E32}" => 'a', "\u{0E19}" => 'n'],
        ]);
        $transliterator = (new Transliterator('never'))->withTable($thai);
        $slugger = new Slugger(new SlugOptions(intlMode: 'never'), FixedClock::at('2026-11-12'), $transliterator);

        $result = $slugger->slug("\u{0E01}\u{0E32}\u{0E19} news");
        self::assertSame('kan-news', $result->slug);
        self::assertSame(['thai-basic', 'latin'], $result->scripts);
    }

    public function testAReplacementTableWinsOverTheBuiltIn(): void
    {
        $custom = new class () implements ScriptTable {
            public function name(): string
            {
                return 'cyrillic';
            }

            public function ranges(): array
            {
                return [[0x0400, 0x04FF]];
            }

            public function map(): array
            {
                return ["\u{0445}" => 'h'];
            }

            public function rules(): array
            {
                return [];
            }
        };
        $base = new Transliterator('never');
        $transliterator = $base->withTable($custom);
        self::assertSame('h', $transliterator->transliterate("\u{0445}")->text);
        self::assertSame('kh', $base->transliterate("\u{0445}")->text, 'withTable() must return a copy.');
    }

    public function testFromArrayValidates(): void
    {
        $this->expectException(InvalidOptionException::class);
        ArrayScriptTable::fromArray(['name' => 'Bad Name', 'type' => 'alphabet', 'ranges' => [[0x0E00, 0x0E7F]], 'classes' => []]);
    }

    public function testNonAsciiOutputIsRejected(): void
    {
        $this->expectException(InvalidOptionException::class);
        new ArrayScriptTable('x', 'alphabet', [[0x0E00, 0x0E7F]], ['letter' => ["\u{0E01}" => "\u{00E9}"]]);
    }

    public function testUnknownClassIsRejected(): void
    {
        $this->expectException(InvalidOptionException::class);
        new ArrayScriptTable('x', 'alphabet', [[0x0E00, 0x0E7F]], ['virama' => ["\u{0E3A}" => '']]);
    }

    public function testAsciiRangesAreRejected(): void
    {
        $this->expectException(InvalidOptionException::class);
        new ArrayScriptTable('x', 'alphabet', [[0x0041, 0x005A]], []);
    }
}
