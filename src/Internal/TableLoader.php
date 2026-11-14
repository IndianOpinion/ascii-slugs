<?php

declare(strict_types=1);

namespace IndianOpinion\AsciiSlugs\Internal;

use IndianOpinion\AsciiSlugs\ArrayScriptTable;
use IndianOpinion\AsciiSlugs\Internal\Engine\AbjadEngine;
use LogicException;

/**
 * Loads built-in tables on first use.
 *
 * Parsed tables are shared by every loader in the process. Each loader also
 * records which tables it was asked for, which tests use to check lazy
 * loading (PRD 12, test 21).
 *
 * @internal
 */
final class TableLoader
{
    /** @var array<string, ArrayScriptTable> */
    private static array $shared = [];

    /** @var list<string> */
    private array $requested = [];

    public function load(string $name): ArrayScriptTable
    {
        if (!isset(BuiltinTables::RANGES[$name])) {
            throw new LogicException(sprintf('There is no built-in table named "%s".', $name));
        }
        if (!in_array($name, $this->requested, true)) {
            $this->requested[] = $name;
        }
        if (!isset(self::$shared[$name])) {
            self::$shared[$name] = self::readFile($name);
        }

        return self::$shared[$name];
    }

    /**
     * Names of the tables this loader has handed out, in order.
     *
     * @return list<string>
     */
    public function loadedNames(): array
    {
        return $this->requested;
    }

    private static function readFile(string $name): ArrayScriptTable
    {
        // The name comes from the fixed index above, never from input.
        $definition = require BuiltinTables::directory() . '/' . $name . '.php';
        if (!is_array($definition)) {
            throw new LogicException(sprintf('Table file "%s" did not return an array.', $name));
        }
        $table = ArrayScriptTable::fromArray($definition, true);

        $exceptionsFile = $table->rule('exceptions');
        if (is_string($exceptionsFile) && preg_match('/^[a-z0-9-]+\.php$/', $exceptionsFile) === 1) {
            $words = require BuiltinTables::directory() . '/' . $exceptionsFile;
            if (is_array($words)) {
                $clean = [];
                foreach ($words as $word => $ascii) {
                    if (is_string($ascii)) {
                        $clean[strtr((string) $word, AbjadEngine::EXCEPTION_FOLD)] = $ascii;
                    }
                }
                $table = $table->withRules(['exceptionWords' => $clean]);
            }
        }

        return $table;
    }
}
