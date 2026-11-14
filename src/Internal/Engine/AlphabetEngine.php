<?php

declare(strict_types=1);

namespace IndianOpinion\AsciiSlugs\Internal\Engine;

use IndianOpinion\AsciiSlugs\ArrayScriptTable;

/**
 * One sequence, one output: Cyrillic, Greek, Latin and custom tables.
 *
 * @internal
 */
final class AlphabetEngine implements Engine
{
    public function render(array $tokens, ArrayScriptTable $table, bool $dropFinalInherentVowel): string
    {
        $out = '';
        foreach ($tokens as $token) {
            $out .= $token->class === 'separator' ? ' ' : $token->output;
        }

        return $out;
    }
}
