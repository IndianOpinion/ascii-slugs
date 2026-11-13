<?php

declare(strict_types=1);

namespace IndianOpinion\AsciiSlugs;

use IndianOpinion\AsciiSlugs\Exception\InvalidInputException;
use IndianOpinion\AsciiSlugs\Exception\InvalidOptionException;
use IndianOpinion\AsciiSlugs\Internal\BuiltinTables;
use IndianOpinion\AsciiSlugs\Internal\Engine\AbjadEngine;
use IndianOpinion\AsciiSlugs\Internal\Engine\AlphabetEngine;
use IndianOpinion\AsciiSlugs\Internal\Engine\BrahmicEngine;
use IndianOpinion\AsciiSlugs\Internal\Engine\Engine;
use IndianOpinion\AsciiSlugs\Internal\TableLoader;
use IndianOpinion\AsciiSlugs\Internal\Token;
use IndianOpinion\AsciiSlugs\Internal\Utf8;
use Throwable;

/**
 * Turns text in any covered script into ASCII, run by run (PRD 4.2, 4.3).
 */
final class Transliterator
{
    public const INTL_AUTO = 'auto';
    public const INTL_NEVER = 'never';
    public const INTL_PREFER = 'prefer';

    private const ICU_RULES = 'Any-Latin; Latin-ASCII';

    /** Scripts named in results when a run has no table (PCRE script names). */
    private const PROBE_SCRIPTS = [
        'Thai', 'Lao', 'Khmer', 'Myanmar', 'Sinhala', 'Tibetan', 'Meetei_Mayek', 'Ol_Chiki', 'Han',
        'Hiragana', 'Katakana', 'Hangul', 'Hebrew', 'Armenian', 'Georgian', 'Ethiopic', 'Syriac',
        'Thaana', 'Mongolian', 'Limbu', 'Lepcha', 'Chakma', 'Tifinagh', 'Cherokee',
    ];

    /** Classes that only make sense after a base letter. */
    private const COMBINING_CLASSES = [
        'sign', 'virama', 'anusvara', 'chandrabindu', 'visarga', 'addak', 'harakat', 'shadda', 'mark',
    ];

    private string $intlMode;

    private bool $dropFinalInherentVowel;

    private TableLoader $loader;

    /** @var array<string, ArrayScriptTable> custom tables by name */
    private array $custom = [];

    /** @var list<array{0: int, 1: int, 2: string}> lo, hi, table name */
    private array $index = [];

    /** @var array<string, string> character to run kind */
    private array $kindCache = [];

    /** @var array<string, Engine> */
    private array $engines = [];

    private static ?\Transliterator $icu = null;

    private static bool $icuTried = false;

    /** @var array<string, bool> */
    private static array $letterCache = [];

    /**
     * @param string $intlMode `auto` (ICU only for scripts with no table),
     *     `never` (no ICU) or `prefer` (ICU for every non-ASCII run).
     *
     * @throws InvalidOptionException for an unknown mode.
     */
    public function __construct(string $intlMode = self::INTL_AUTO, bool $dropFinalInherentVowel = true)
    {
        if (!in_array($intlMode, [self::INTL_AUTO, self::INTL_NEVER, self::INTL_PREFER], true)) {
            throw new InvalidOptionException('intlMode must be "auto", "never" or "prefer".');
        }
        $this->intlMode = $intlMode;
        $this->dropFinalInherentVowel = $dropFinalInherentVowel;
        $this->loader = new TableLoader();
        $this->buildIndex();
    }

    /**
     * Whether ICU transliteration is available on this host.
     */
    public static function intlAvailable(): bool
    {
        return self::icu() !== null;
    }

    /**
     * The ICU version, or null without intl.
     */
    public static function icuVersion(): ?string
    {
        return defined('INTL_ICU_VERSION') ? (string) constant('INTL_ICU_VERSION') : null;
    }

    /**
     * Names of the scripts with a built-in table.
     *
     * @return list<string>
     */
    public static function builtinScripts(): array
    {
        return BuiltinTables::names();
    }

    /**
     * A copy with an extra table, or one that replaces a table of the same
     * name. Its ranges take precedence over the built-in ranges.
     *
     * @throws InvalidOptionException when the table is malformed.
     */
    public function withTable(ScriptTable $table): self
    {
        $copy = clone $this;
        $copy->loader = new TableLoader();
        $compiled = ArrayScriptTable::fromTable($table);
        $copy->custom[$compiled->name()] = $compiled;
        $copy->kindCache = [];
        $copy->buildIndex();

        return $copy;
    }

    public function intlMode(): string
    {
        return $this->intlMode;
    }

    /**
     * Built-in tables this instance has loaded so far, in order.
     *
     * @return list<string>
     */
    public function loadedTables(): array
    {
        return $this->loader->loadedNames();
    }

    /**
     * @throws InvalidInputException when the text is not valid UTF-8.
     */
    public function transliterate(string $text): TransliterationResult
    {
        if (!Utf8::isValid($text)) {
            throw new InvalidInputException('The text is not valid UTF-8.');
        }

        $out = '';
        $letters = 0;
        $covered = 0;
        $scripts = [];
        $uncovered = [];
        $usedIntl = false;

        foreach ($this->segment(Utf8::split($text)) as [$kind, $chars]) {
            $runText = implode('', $chars);
            if ($kind === '#ascii') {
                [$piece, $runLetters, $runCovered] = $this->renderAscii($chars);
                $script = $runLetters > 0 ? 'latin' : null;
            } elseif ($kind === '#common') {
                $out .= ' ';
                continue;
            } elseif ($kind === '#stray') {
                $piece = '';
                $runLetters = $this->countLetters($chars);
                $runCovered = 0;
                $script = null;
            } elseif ($kind === '#unknown') {
                $script = $this->probeScript($chars[0]);
                $runLetters = $this->countLetters($chars);
                $icu = $this->intlMode === self::INTL_NEVER ? null : $this->icuRun($runText);
                if ($icu !== null) {
                    $piece = $icu;
                    $runCovered = $runLetters;
                    $usedIntl = true;
                } else {
                    $piece = ' ';
                    $runCovered = 0;
                }
            } else {
                $table = $this->table($kind);
                $script = $table->name();
                $icu = $this->intlMode === self::INTL_PREFER ? $this->icuRun($runText) : null;
                if ($icu !== null) {
                    $piece = $icu;
                    $runLetters = $this->countLetters($chars);
                    $runCovered = $runLetters;
                    $usedIntl = true;
                } else {
                    [$piece, $runLetters, $runCovered] = $this->renderTable($chars, $table);
                }
            }

            $out .= $piece;
            $letters += $runLetters;
            $covered += $runCovered;
            if ($script !== null && $runLetters > 0) {
                if (!in_array($script, $scripts, true)) {
                    $scripts[] = $script;
                }
                if ($runCovered < $runLetters && !in_array($script, $uncovered, true)) {
                    $uncovered[] = $script;
                }
            }
        }

        return new TransliterationResult($out, $letters, $covered, $scripts, $uncovered, $usedIntl);
    }

    private function buildIndex(): void
    {
        $index = [];
        foreach ($this->custom as $name => $table) {
            foreach ($table->ranges() as [$lo, $hi]) {
                $index[] = [$lo, $hi, $name];
            }
        }
        foreach (BuiltinTables::RANGES as $name => $ranges) {
            if (isset($this->custom[$name])) {
                continue;
            }
            foreach ($ranges as [$lo, $hi]) {
                $index[] = [$lo, $hi, $name];
            }
        }
        $this->index = $index;
    }

    /**
     * A table name, `#mark`, `#unknown` or `#common` for one non-ASCII character.
     */
    private function classify(string $char): string
    {
        if (count($this->kindCache) > 8192) {
            $this->kindCache = [];
        }
        $table = $this->tableFor(Utf8::ord($char));
        if ($table !== null) {
            return $table;
        }
        if (preg_match('/^\p{M}$/u', $char) === 1) {
            return '#mark';
        }

        return preg_match('/^[\p{L}\p{N}]$/u', $char) === 1 ? '#unknown' : '#common';
    }

    private function tableFor(int $codePoint): ?string
    {
        foreach ($this->index as [$lo, $hi, $name]) {
            if ($codePoint >= $lo && $codePoint <= $hi) {
                return $name;
            }
        }

        return null;
    }

    private function table(string $name): ArrayScriptTable
    {
        return $this->custom[$name] ?? $this->loader->load($name);
    }

    /**
     * Splits code points into runs. A run kind is a table name or one of
     * `#ascii`, `#common`, `#stray` and `#unknown`. Table names cannot start
     * with `#`, so the two never clash.
     *
     * @param list<string> $chars
     *
     * @return list<array{0: string, 1: list<string>}>
     */
    private function segment(array $chars): array
    {
        $runs = [];
        $current = null;
        $buffer = [];
        foreach ($chars as $char) {
            if (!isset($char[1])) {
                $kind = '#ascii';
            } else {
                $kind = $this->kindCache[$char] ??= $this->classify($char);
                if ($kind === '#mark') {
                    // Combining marks belong to the run before them.
                    $kind = ($current === null || $current === '#common') ? '#stray' : $current;
                }
            }
            if ($kind !== $current && $current !== null) {
                $runs[] = [$current, $buffer];
                $buffer = [];
            }
            $current = $kind;
            $buffer[] = $char;
        }
        if ($current !== null) {
            $runs[] = [$current, $buffer];
        }

        return $runs;
    }

    /**
     * @param list<string> $chars
     *
     * @return array{0: string, 1: int, 2: int}
     */
    private function renderAscii(array $chars): array
    {
        $out = '';
        $letters = 0;
        $covered = 0;
        foreach ($chars as $char) {
            if (strlen($char) === 1) {
                $out .= $char;
                if (ctype_alpha($char)) {
                    $letters++;
                    $covered++;
                }
                continue;
            }
            // A combining mark carried into an ASCII run: e + U+0301.
            $letters++;
            if (self::isCombiningDiacritic(Utf8::ord($char))) {
                $covered++;
            }
        }

        return [$out, $letters, $covered];
    }

    /**
     * @param list<string> $chars
     *
     * @return array{0: string, 1: int, 2: int}
     */
    private function renderTable(array $chars, ArrayScriptTable $table): array
    {
        $tokens = $this->tokenize($chars, $table);
        $letters = 0;
        $covered = 0;
        foreach ($tokens as $token) {
            $letters += $token->letters;
            if ($token->class !== null) {
                $covered += $token->letters;
            }
        }

        return [$this->engine($table->type())->render($tokens, $table, $this->dropFinalInherentVowel), $letters, $covered];
    }

    /**
     * Greedy longest match against the table.
     *
     * @param list<string> $chars
     *
     * @return list<Token>
     */
    private function tokenize(array $chars, ArrayScriptTable $table): array
    {
        $tokens = [];
        $count = count($chars);
        $max = $table->maxSequenceLength();
        $classOf = $table->classes();
        $map = $table->map();
        $dropDiacritics = $table->rule('dropCombiningDiacritics', false) === true;
        for ($i = 0; $i < $count;) {
            $char = $chars[$i];
            if ($max > 1 && $table->startsSequence($char)) {
                for ($length = min($max, $count - $i); $length > 1; $length--) {
                    $parts = array_slice($chars, $i, $length);
                    $sequence = implode('', $parts);
                    if (isset($classOf[$sequence])) {
                        $tokens[] = new Token($sequence, $classOf[$sequence], $map[$sequence], $this->countLetters($parts));
                        $i += $length;
                        continue 2;
                    }
                }
            }
            $letters = self::$letterCache[$char] ??= self::isLetter($char);
            $class = $classOf[$char] ?? null;
            if ($class === null) {
                if ($dropDiacritics && self::isCombiningDiacritic(Utf8::ord($char))) {
                    $class = 'mark';
                } elseif (!$letters) {
                    // Punctuation or symbols inside the block, such as U+00D7.
                    $class = 'separator';
                }
            }
            $tokens[] = new Token($char, $class, $map[$char] ?? '', $letters ? 1 : 0);
            $i++;
        }

        // Signs with no base letter at the start of a run follow a character
        // of another script: bad input, dropped and not covered (PRD 4.8.5).
        foreach ($tokens as $k => $token) {
            if (!in_array($token->class, self::COMBINING_CLASSES, true) || $token->letters === 0) {
                break;
            }
            $tokens[$k] = new Token($token->sequence, null, '', $token->letters);
        }
        return $tokens;
    }

    private function engine(string $type): Engine
    {
        if (!isset($this->engines[$type])) {
            $this->engines[$type] = match ($type) {
                'brahmic' => new BrahmicEngine(),
                'abjad' => new AbjadEngine(),
                default => new AlphabetEngine(),
            };
        }

        return $this->engines[$type];
    }

    /**
     * @param list<string> $chars
     */
    private function countLetters(array $chars): int
    {
        $count = 0;
        foreach ($chars as $char) {
            if (self::$letterCache[$char] ??= self::isLetter($char)) {
                $count++;
            }
        }

        return $count;
    }

    private static function isLetter(string $char): bool
    {
        if (count(self::$letterCache) > 8192) {
            self::$letterCache = [];
        }

        return preg_match('/^[\p{L}\p{M}]$/u', $char) === 1;
    }

    private static function isCombiningDiacritic(int $codePoint): bool
    {
        return ($codePoint >= 0x0300 && $codePoint <= 0x036F)
            || ($codePoint >= 0x1AB0 && $codePoint <= 0x1AFF)
            || ($codePoint >= 0x1DC0 && $codePoint <= 0x1DFF)
            || ($codePoint >= 0x20D0 && $codePoint <= 0x20FF)
            || ($codePoint >= 0xFE20 && $codePoint <= 0xFE2F);
    }

    private function probeScript(string $char): string
    {
        foreach (self::PROBE_SCRIPTS as $script) {
            if (preg_match('/^\p{' . $script . '}/u', $char) === 1) {
                return strtolower(str_replace('_', '-', $script));
            }
        }

        return 'other';
    }

    /**
     * ICU on one run. Null when ICU is missing, fails, or gives nothing usable.
     */
    private function icuRun(string $text): ?string
    {
        $icu = self::icu();
        if ($icu === null) {
            return null;
        }
        try {
            $result = $icu->transliterate($text);
        } catch (Throwable) {
            return null;
        }
        if (!is_string($result) || preg_match('/[A-Za-z0-9]/', $result) !== 1) {
            return null;
        }

        return $result;
    }

    private static function icu(): ?\Transliterator
    {
        if (!self::$icuTried) {
            self::$icuTried = true;
            if (extension_loaded('intl') && class_exists(\Transliterator::class)) {
                try {
                    $icu = \Transliterator::create(self::ICU_RULES);
                    self::$icu = $icu instanceof \Transliterator ? $icu : null;
                } catch (Throwable) {
                    self::$icu = null;
                }
            }
        }

        return self::$icu;
    }
}
