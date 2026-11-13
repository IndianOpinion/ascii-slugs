<?php

declare(strict_types=1);

namespace IndianOpinion\AsciiSlugs\Internal\Engine;

use IndianOpinion\AsciiSlugs\ArrayScriptTable;
use IndianOpinion\AsciiSlugs\Internal\Token;

/**
 * Turns the tokens of one run into ASCII.
 *
 * @internal
 */
interface Engine
{
    /**
     * @param list<Token> $tokens
     */
    public function render(array $tokens, ArrayScriptTable $table, bool $dropFinalInherentVowel): string;
}
