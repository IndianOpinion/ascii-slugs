<?php

declare(strict_types=1);

namespace IndianOpinion\AsciiSlugs\Tests\Unit;

use IndianOpinion\AsciiSlugs\Exception\InvalidOptionException;
use IndianOpinion\AsciiSlugs\SlugOptions;
use IndianOpinion\AsciiSlugs\Transliterator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * PRD section 7: every option is validated.
 */
final class OptionsTest extends TestCase
{
    public function testDefaults(): void
    {
        self::assertSame([
            'maxLength' => 80,
            'minCutPosition' => 20,
            'minAlnum' => 3,
            'minCoverage' => 0.5,
            'fallbackPrefix' => 'post',
            'hashLength' => 8,
            'includeDate' => true,
            'intlMode' => 'auto',
            'dropFinalInherentVowel' => true,
        ], (new SlugOptions())->toArray());
    }

    /**
     * @return iterable<string, array{array<string, mixed>}>
     */
    public static function invalid(): iterable
    {
        yield 'maxLength low' => [['maxLength' => 19]];
        yield 'maxLength high' => [['maxLength' => 191]];
        yield 'minCutPosition zero' => [['minCutPosition' => 0]];
        yield 'minCutPosition above maxLength' => [['maxLength' => 30, 'minCutPosition' => 31]];
        yield 'minAlnum zero' => [['minAlnum' => 0]];
        yield 'minAlnum high' => [['minAlnum' => 21]];
        yield 'minCoverage negative' => [['minCoverage' => -0.1]];
        yield 'minCoverage high' => [['minCoverage' => 1.5]];
        yield 'hashLength low' => [['hashLength' => 5]];
        yield 'hashLength high' => [['hashLength' => 17]];
        yield 'intlMode' => [['intlMode' => 'always']];
        yield 'unknown key' => [['maxlength' => 80]];
        yield 'wrong type' => [['maxLength' => '80']];
        yield 'wrong bool' => [['includeDate' => 1]];
        yield 'list' => [[80]];
    }

    /**
     * @param array<mixed> $values
     */
    #[DataProvider('invalid')]
    public function testInvalidValuesThrow(array $values): void
    {
        $this->expectException(InvalidOptionException::class);
        SlugOptions::fromArray($values);
    }

    public function testFromArrayRoundTrip(): void
    {
        $options = SlugOptions::fromArray(['maxLength' => 120, 'minCoverage' => 1, 'fallbackPrefix' => 'story']);
        self::assertSame(120, $options->maxLength);
        self::assertSame(1.0, $options->minCoverage);
        self::assertSame('story', $options->fallbackPrefix);
        self::assertEquals($options, SlugOptions::fromArray($options->toArray()));
    }

    public function testWithersReturnCopies(): void
    {
        $base = new SlugOptions();
        $changed = $base
            ->withMaxLength(40)
            ->withMinCutPosition(10)
            ->withMinAlnum(4)
            ->withMinCoverage(0.75)
            ->withFallbackPrefix('t')
            ->withHashLength(10)
            ->withIncludeDate(false)
            ->withIntlMode('never')
            ->withDropFinalInherentVowel(false);

        self::assertSame(80, $base->maxLength);
        self::assertSame([
            'maxLength' => 40, 'minCutPosition' => 10, 'minAlnum' => 4, 'minCoverage' => 0.75,
            'fallbackPrefix' => 't', 'hashLength' => 10, 'includeDate' => false, 'intlMode' => 'never',
            'dropFinalInherentVowel' => false,
        ], $changed->toArray());
    }

    public function testShrinkingMaxLengthKeepsMinCutPositionValid(): void
    {
        self::assertSame(20, (new SlugOptions(maxLength: 100, minCutPosition: 60))->withMaxLength(20)->minCutPosition);
    }

    public function testTransliteratorRejectsUnknownMode(): void
    {
        $this->expectException(InvalidOptionException::class);
        new Transliterator('sometimes');
    }
}
