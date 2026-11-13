<?php

declare(strict_types=1);

namespace IndianOpinion\AsciiSlugs;

use IndianOpinion\AsciiSlugs\Exception\InvalidOptionException;
use IndianOpinion\AsciiSlugs\Internal\Utf8;

/**
 * A script table built from a plain PHP array.
 *
 * The built-in tables under `tables/` use this format:
 *
 *     [
 *         'name' => 'devanagari',
 *         'type' => 'brahmic',
 *         'ranges' => [[0x0900, 0x097F]],
 *         'rules' => ['inherentVowel' => 'a', ...],
 *         'classes' => ['consonant' => ["\u{0915}" => 'k', ...], ...],
 *     ]
 */
final class ArrayScriptTable implements ScriptTable
{
    private const TYPES = [
        'brahmic' => [
            'consonant', 'dead', 'vowel', 'sign', 'virama', 'anusvara', 'chandrabindu',
            'visarga', 'addak', 'mark', 'digit', 'separator', 'other',
        ],
        'abjad' => ['consonant', 'vowel', 'yeh', 'waw', 'harakat', 'shadda', 'mark', 'digit', 'separator'],
        'alphabet' => ['letter', 'mark', 'digit', 'separator'],
    ];

    private string $name;

    private string $type;

    /** @var list<array{0: int, 1: int}> */
    private array $ranges;

    /** @var array<string, string> */
    private array $map = [];

    /** @var array<string, string> */
    private array $classOf = [];

    /** @var array<string, mixed> */
    private array $rules;

    private int $maxSequenceLength = 1;

    /** @var array<string, true> first code point of every multi-code-point key */
    private array $multiStarts = [];

    /**
     * @param list<array{0: int, 1: int}> $ranges
     * @param array<string, array<string, string>> $classes class name to [sequence => ASCII]
     * @param array<string, mixed> $rules
     * @param bool $trusted Skip the per-entry checks (built-in tables only).
     *
     * @throws InvalidOptionException when the definition is malformed.
     */
    public function __construct(string $name, string $type, array $ranges, array $classes, array $rules = [], bool $trusted = false)
    {
        if (preg_match('/^[a-z][a-z0-9-]{0,39}$/', $name) !== 1) {
            throw new InvalidOptionException('A table name must be lower-case ASCII, for example "devanagari".');
        }
        if (!isset(self::TYPES[$type])) {
            throw new InvalidOptionException(sprintf('Unknown table type "%s".', $type));
        }
        if ($ranges === []) {
            throw new InvalidOptionException(sprintf('Table "%s" has no code point ranges.', $name));
        }
        foreach ($ranges as $range) {
            if ($range[0] < 0x80 || $range[1] < $range[0] || $range[1] > 0x10FFFF) {
                throw new InvalidOptionException(sprintf('Table "%s" has an invalid range.', $name));
            }
        }
        foreach ($classes as $class => $entries) {
            if (!in_array($class, self::TYPES[$type], true)) {
                throw new InvalidOptionException(sprintf('Table "%s" uses class "%s", which a %s table does not have.', $name, $class, $type));
            }
            foreach ($entries as $sequence => $ascii) {
                $sequence = (string) $sequence;
                if (!$trusted && ($sequence === '' || !Utf8::isValid($sequence) || preg_match('/^[\x20-\x7E]*$/', $ascii) !== 1)) {
                    throw new InvalidOptionException(sprintf('Table "%s" has an invalid entry in class "%s".', $name, $class));
                }
                $this->map[$sequence] = $ascii;
                $this->classOf[$sequence] = $class;
                $length = Utf8::length($sequence);
                if ($length > 1) {
                    $this->multiStarts[Utf8::first($sequence)] = true;
                    $this->maxSequenceLength = max($this->maxSequenceLength, $length);
                }
            }
        }

        $this->name = $name;
        $this->type = $type;
        $this->ranges = $ranges;
        $this->rules = $rules;
    }

    /**
     * Builds a table from the array format described on the class.
     *
     * @param array<mixed> $definition
     * @param bool $trusted Skip the per-entry checks. Only for the built-in
     *     tables, which the test suite checks instead.
     *
     * @throws InvalidOptionException when the definition is malformed.
     */
    public static function fromArray(array $definition, bool $trusted = false): self
    {
        $name = $definition['name'] ?? null;
        $type = $definition['type'] ?? 'alphabet';
        $ranges = $definition['ranges'] ?? null;
        $classes = $definition['classes'] ?? null;
        $rules = $definition['rules'] ?? [];
        if (!is_string($name) || !is_string($type) || !is_array($ranges) || !is_array($classes) || !is_array($rules)) {
            throw new InvalidOptionException('A table definition needs a name, a type, ranges and classes.');
        }

        $cleanRanges = [];
        foreach ($ranges as $range) {
            if (!is_array($range) || !isset($range[0], $range[1]) || !is_int($range[0]) || !is_int($range[1])) {
                throw new InvalidOptionException('Each range must be a pair of integers.');
            }
            $cleanRanges[] = [$range[0], $range[1]];
        }

        $cleanClasses = [];
        foreach ($classes as $class => $entries) {
            if (!is_string($class) || !is_array($entries)) {
                throw new InvalidOptionException('Each class must map sequences to ASCII strings.');
            }
            foreach ($entries as $sequence => $ascii) {
                if (!is_string($ascii)) {
                    throw new InvalidOptionException('Each class must map sequences to ASCII strings.');
                }
                $cleanClasses[$class][(string) $sequence] = $ascii;
            }
        }

        $cleanRules = [];
        foreach ($rules as $key => $value) {
            $cleanRules[(string) $key] = $value;
        }

        return new self($name, $type, $cleanRanges, $cleanClasses, $cleanRules, $trusted);
    }

    /**
     * Compiles any ScriptTable into this class.
     *
     * @throws InvalidOptionException when the table is malformed.
     */
    public static function fromTable(ScriptTable $table): self
    {
        if ($table instanceof self) {
            return $table;
        }
        $rules = $table->rules();
        $type = $rules['type'] ?? 'alphabet';
        $classOf = $rules['classes'] ?? [];
        if (!is_string($type) || !is_array($classOf)) {
            throw new InvalidOptionException(sprintf('Table "%s" has invalid rules.', $table->name()));
        }
        $classes = [];
        foreach ($table->map() as $sequence => $ascii) {
            $class = $classOf[$sequence] ?? ($type === 'alphabet' ? 'letter' : null);
            if (!is_string($class)) {
                throw new InvalidOptionException(sprintf('Table "%s" gives no class for a sequence.', $table->name()));
            }
            $classes[$class][(string) $sequence] = $ascii;
        }
        unset($rules['type'], $rules['classes']);

        return new self($table->name(), $type, $table->ranges(), $classes, $rules);
    }

    public function name(): string
    {
        return $this->name;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function ranges(): array
    {
        return $this->ranges;
    }

    public function map(): array
    {
        return $this->map;
    }

    public function rules(): array
    {
        return ['type' => $this->type, 'classes' => $this->classOf] + $this->rules;
    }

    /**
     * A single rule value, or the default when it is missing.
     */
    public function rule(string $key, mixed $default = null): mixed
    {
        return $this->rules[$key] ?? $default;
    }

    /**
     * Sequence to class name.
     *
     * @return array<string, string>
     */
    public function classes(): array
    {
        return $this->classOf;
    }

    public function classOf(string $sequence): ?string
    {
        return $this->classOf[$sequence] ?? null;
    }

    public function output(string $sequence): string
    {
        return $this->map[$sequence] ?? '';
    }

    /**
     * The longest key, in code points.
     */
    public function maxSequenceLength(): int
    {
        return $this->maxSequenceLength;
    }

    /**
     * Whether some key longer than one code point starts with $codePoint.
     */
    public function startsSequence(string $codePoint): bool
    {
        return isset($this->multiStarts[$codePoint]);
    }

    /**
     * Every key of one class.
     *
     * @return list<string>
     */
    public function sequencesOf(string $class): array
    {
        $keys = [];
        foreach ($this->classOf as $sequence => $sequenceClass) {
            if ($sequenceClass === $class) {
                $keys[] = (string) $sequence;
            }
        }

        return $keys;
    }

    /**
     * A copy with extra rules merged in.
     *
     * @param array<string, mixed> $rules
     */
    public function withRules(array $rules): self
    {
        $copy = clone $this;
        $copy->rules = $rules + $this->rules;

        return $copy;
    }
}
