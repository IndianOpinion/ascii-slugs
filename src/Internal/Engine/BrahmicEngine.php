<?php

declare(strict_types=1);

namespace IndianOpinion\AsciiSlugs\Internal\Engine;

use IndianOpinion\AsciiSlugs\ArrayScriptTable;

/**
 * Indic abugidas: inherent vowel, matras, virama, anusvara (PRD 4.2).
 *
 * @internal
 */
final class BrahmicEngine implements Engine
{
    /**
     * A word-final conjunct ending in one of these keeps its inherent vowel:
     * mitra, rashtra, satya, tattva. Other final conjuncts drop it: taiks,
     * rikord.
     */
    private const SOUNDED_CONJUNCT_ENDINGS = ['r', 'y', 'v', 'gy'];

    private string $out = '';

    /** @var array<string, list<string>> virama keys per table */
    private array $viramas = [];

    private string $inherent = 'a';

    private bool $dropFinal = false;

    /** A consonant has been written and still waits for its vowel. */
    private bool $pending = false;

    /** The pending consonant closes a conjunct. */
    private bool $pendingConjunct = false;

    /** Letters (consonants and vowels) seen in the current word. */
    private int $wordLetters = 0;

    public function render(array $tokens, ArrayScriptTable $table, bool $dropFinalInherentVowel): string
    {
        $inherent = $table->rule('inherentVowel', 'a');
        $this->inherent = is_string($inherent) ? $inherent : 'a';
        $this->dropFinal = $dropFinalInherentVowel && $table->rule('dropFinalInherentVowel', false) === true;
        $anusvara = $table->rule('anusvara', 'assimilate');
        $anusvaraMode = is_string($anusvara) ? $anusvara : 'assimilate';
        $final = $table->rule('anusvaraFinal', 'n');
        $anusvaraFinal = is_string($final) ? $final : 'n';
        if (!isset($this->viramas[$table->name()])) {
            $this->viramas[$table->name()] = $table->sequencesOf('virama');
        }
        $viramas = $this->viramas[$table->name()];

        $this->out = '';
        $this->pending = false;
        $this->pendingConjunct = false;
        $this->wordLetters = 0;
        $double = false;
        $afterVirama = false;
        $count = count($tokens);

        for ($i = 0; $i < $count; $i++) {
            $token = $tokens[$i];
            switch ($token->class) {
                case 'consonant':
                    $this->flushInherent();
                    $output = $token->output;
                    if ($double && $output !== '') {
                        $output = (str_starts_with($output, 'ch') ? 'ch' : $output[0]) . $output;
                    }
                    $double = false;
                    $this->out .= $output;
                    $this->pending = true;
                    $this->pendingConjunct = ($afterVirama || $this->containsAny($token->sequence, $viramas))
                        && in_array($token->output, self::SOUNDED_CONJUNCT_ENDINGS, true);
                    $this->wordLetters++;
                    break;
                case 'dead':
                    $this->flushInherent();
                    $this->out .= $token->output;
                    $this->wordLetters++;
                    break;
                case 'sign':
                    $this->out .= $token->output;
                    $this->pending = false;
                    break;
                case 'virama':
                    $this->pending = false;
                    break;
                case 'vowel':
                    $this->flushInherent();
                    $this->out .= $token->output;
                    $this->wordLetters++;
                    break;
                case 'anusvara':
                    $this->flushInherent();
                    $this->out .= $this->anusvara($tokens, $i + 1, $anusvaraMode, $anusvaraFinal);
                    break;
                case 'chandrabindu':
                case 'visarga':
                    $this->flushInherent();
                    $this->out .= $token->output;
                    break;
                case 'addak':
                    $double = true;
                    break;
                case 'other':
                    $this->endWord();
                    $this->out .= ' ' . $token->output . ' ';
                    break;
                case 'digit':
                    $this->endWord();
                    $this->out .= $token->output;
                    break;
                case 'separator':
                    $this->endWord();
                    $this->out .= ' ';
                    break;
                default:
                    // Marks (nukta on its own, avagraha) and unmapped code points.
                    break;
            }
            $afterVirama = $token->class === 'virama';
        }
        $this->endWord();

        return $this->out;
    }

    private function flushInherent(): void
    {
        if ($this->pending) {
            $this->out .= $this->inherent;
            $this->pending = false;
        }
    }

    private function endWord(): void
    {
        if ($this->pending) {
            $keep = !$this->dropFinal || $this->wordLetters < 2 || $this->pendingConjunct;
            if ($keep) {
                $this->out .= $this->inherent;
            }
            $this->pending = false;
        }
        $this->wordLetters = 0;
    }

    /**
     * @param list<\IndianOpinion\AsciiSlugs\Internal\Token> $tokens
     */
    private function anusvara(array $tokens, int $next, string $mode, string $final): string
    {
        if ($mode !== 'assimilate') {
            return $mode;
        }
        $count = count($tokens);
        for ($j = $next; $j < $count; $j++) {
            $class = $tokens[$j]->class;
            if ($class === 'mark' || $class === null) {
                continue;
            }
            if ($class === 'consonant' || $class === 'dead') {
                $first = $tokens[$j]->output[0] ?? '';

                return in_array($first, ['p', 'b', 'm'], true) ? 'm' : 'n';
            }
            break;
        }

        return $final;
    }

    /**
     * @param list<string> $needles
     */
    private function containsAny(string $haystack, array $needles): bool
    {
        foreach ($needles as $needle) {
            if (str_contains($haystack, $needle)) {
                return true;
            }
        }

        return false;
    }
}
