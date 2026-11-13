<?php

declare(strict_types=1);

namespace IndianOpinion\AsciiSlugs\Internal\Engine;

use IndianOpinion\AsciiSlugs\ArrayScriptTable;
use IndianOpinion\AsciiSlugs\Internal\Token;

/**
 * Arabic script with Urdu rules (PRD 4.2).
 *
 * @internal
 */
final class AbjadEngine implements Engine
{
    private const WORD_CLASSES = ['consonant', 'vowel', 'yeh', 'waw', 'harakat', 'shadda', 'mark', null];

    private const SPELLING_CLASSES = ['consonant', 'vowel', 'yeh', 'waw'];

    public function render(array $tokens, ArrayScriptTable $table, bool $dropFinalInherentVowel): string
    {
        $exceptions = $table->rule('exceptionWords', []);
        $exceptions = is_array($exceptions) ? $exceptions : [];

        $out = '';
        $word = [];
        foreach ($tokens as $token) {
            if (in_array($token->class, self::WORD_CLASSES, true)) {
                $word[] = $token;
                continue;
            }
            $out .= $this->word($word, $exceptions);
            $word = [];
            $out .= $token->class === 'digit' ? $token->output : ' ';
        }

        return $out . $this->word($word, $exceptions);
    }

    /**
     * @param list<Token> $tokens
     * @param array<mixed> $exceptions
     */
    private function word(array $tokens, array $exceptions): string
    {
        if ($tokens === []) {
            return '';
        }

        $key = '';
        foreach ($tokens as $token) {
            if (in_array($token->class, self::SPELLING_CLASSES, true)) {
                $key .= $token->sequence;
            }
        }
        if (isset($exceptions[$key]) && is_string($exceptions[$key])) {
            return $exceptions[$key];
        }

        $out = '';
        $start = true;
        $afterVowelLetter = false;
        $lastConsonant = '';
        $lastConsonantEnd = 0;
        foreach ($tokens as $token) {
            switch ($token->class) {
                case 'consonant':
                    $out .= $token->output;
                    $lastConsonant = $token->output;
                    $lastConsonantEnd = strlen($out);
                    $afterVowelLetter = false;
                    $start = false;
                    break;
                case 'vowel':
                    $out .= $token->output;
                    $lastConsonant = '';
                    $afterVowelLetter = true;
                    $start = false;
                    break;
                case 'yeh':
                case 'waw':
                    $semivowel = $token->class === 'yeh' ? 'y' : 'w';
                    if ($start || $afterVowelLetter) {
                        $out .= $semivowel;
                        $lastConsonant = $semivowel;
                        $lastConsonantEnd = strlen($out);
                        $afterVowelLetter = false;
                    } else {
                        $out .= $token->class === 'yeh' ? 'i' : 'o';
                        $lastConsonant = '';
                        $afterVowelLetter = true;
                    }
                    $start = false;
                    break;
                case 'harakat':
                    $out .= $token->output;
                    break;
                case 'shadda':
                    // Canonical order puts a short vowel mark before the
                    // shadda, so the doubled letter goes back in front of it.
                    if (strlen($lastConsonant) === 1) {
                        $out = substr($out, 0, $lastConsonantEnd) . $lastConsonant . substr($out, $lastConsonantEnd);
                        $lastConsonantEnd++;
                    }
                    break;
                default:
                    break;
            }
        }

        return $out;
    }
}
