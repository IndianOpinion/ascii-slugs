<?php

declare(strict_types=1);

namespace IndianOpinion\AsciiSlugs\Tests\Unit;

use IndianOpinion\AsciiSlugs\FixedClock;
use IndianOpinion\AsciiSlugs\SlugOptions;
use IndianOpinion\AsciiSlugs\Slugger;
use PHPUnit\Framework\TestCase;

/**
 * Acceptance test 24: output shape over 1,000 generated mixed-script strings.
 */
final class PropertyTest extends TestCase
{
    /** Code point ranges to draw from: covered scripts, Thai, Han, punctuation. */
    private const POOLS = [
        [0x0020, 0x007E], [0x00A0, 0x024F], [0x0300, 0x036F], [0x0370, 0x03FF], [0x0400, 0x04FF],
        [0x0600, 0x06FF], [0x0900, 0x097F], [0x0980, 0x09FF], [0x0A00, 0x0A7F], [0x0A80, 0x0AFF],
        [0x0B00, 0x0B7F], [0x0B80, 0x0BFF], [0x0C00, 0x0C7F], [0x0C80, 0x0CFF], [0x0D00, 0x0D7F],
        [0x0E00, 0x0E7F], [0x2000, 0x206F], [0x4E00, 0x4E80], [0x1F00, 0x1FFF], [0xFE70, 0xFEFF],
    ];

    public function testOutputIsAlwaysAValidSlug(): void
    {
        mt_srand(20261112);
        $slugger = new Slugger(new SlugOptions(intlMode: 'never'), FixedClock::at('2026-11-12'));
        $withIntl = new Slugger(new SlugOptions(intlMode: 'auto', maxLength: 40), FixedClock::at('2026-11-12'));

        for ($i = 0; $i < 1000; $i++) {
            $title = self::randomTitle(mt_rand(1, 60));
            foreach ([$slugger, $withIntl] as $s) {
                $result = $s->slug($title);
                self::assertMatchesRegularExpression('/^[a-z0-9]+(-[a-z0-9]+)*$/', $result->slug, bin2hex($title));
                self::assertLessThanOrEqual($s->options()->maxLength, strlen($result->slug));
                self::assertGreaterThanOrEqual(0.0, $result->coverage);
                self::assertLessThanOrEqual(1.0, $result->coverage);
            }
        }
    }

    private static function randomTitle(int $length): string
    {
        $title = '';
        for ($j = 0; $j < $length; $j++) {
            [$lo, $hi] = self::POOLS[mt_rand(0, count(self::POOLS) - 1)];
            $codePoint = mt_rand($lo, $hi);
            $char = html_entity_decode('&#' . $codePoint . ';', ENT_QUOTES | ENT_HTML5, 'UTF-8');
            if (preg_match('//u', $char) === 1 && !str_starts_with($char, '&#')) {
                $title .= $char;
            }
        }

        return $title;
    }
}
