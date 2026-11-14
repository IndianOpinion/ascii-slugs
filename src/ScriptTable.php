<?php

declare(strict_types=1);

namespace IndianOpinion\AsciiSlugs;

/**
 * A transliteration table for one script.
 *
 * map() gives the ASCII output for each code point sequence. rules() states
 * how the sequences behave:
 *
 * - `type`: `brahmic` (Indic abugida), `abjad` (Arabic script) or
 *   `alphabet` (one sequence, one output). Defaults to `alphabet`.
 * - `classes`: sequence to class name. Brahmic classes are `consonant`,
 *   `dead`, `vowel`, `sign`, `virama`, `anusvara`, `chandrabindu`,
 *   `visarga`, `addak`, `mark`, `digit`, `separator` and `other`. Abjad
 *   classes are `consonant`, `vowel`, `yeh`, `waw`, `harakat`, `shadda`,
 *   `mark`, `digit` and `separator`. Alphabet tables use `letter`, `mark`,
 *   `digit` and `separator`. A sequence with no class is a `letter`.
 * - Brahmic only: `inherentVowel` (string), `dropFinalInherentVowel`
 *   (bool), `anusvara` (`assimilate` or a fixed ASCII string).
 * - Abjad only: `exceptionWords`, whole words mapped to ASCII.
 * - Alphabet only: `dropCombiningDiacritics` (bool).
 */
interface ScriptTable
{
    /**
     * Lower-case script name, for example `devanagari`.
     */
    public function name(): string;

    /**
     * Inclusive code point ranges this table owns.
     *
     * @return list<array{0: int, 1: int}>
     */
    public function ranges(): array;

    /**
     * Code point sequence to ASCII output.
     *
     * @return array<string, string>
     */
    public function map(): array;

    /**
     * Script rules; see the interface description.
     *
     * @return array<string, mixed>
     */
    public function rules(): array;
}
