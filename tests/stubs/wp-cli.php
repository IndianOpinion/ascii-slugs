<?php

/**
 * A stand-in for the parts of WP-CLI the command uses.
 */

declare(strict_types=1);

namespace {
    final class WpCliHalt extends RuntimeException
    {
    }

    class WP_CLI
    {
        /** @var list<array{0: string, 1: string}> */
        public static array $out = [];

        /** @var array<string, object> */
        public static array $commands = [];

        public static function reset(): void
        {
            self::$out = [];
            self::$commands = [];
        }

        public static function add_command(string $name, object $command): void
        {
            self::$commands[$name] = $command;
        }

        public static function log(string $message): void
        {
            self::$out[] = ['log', $message];
        }

        public static function success(string $message): void
        {
            self::$out[] = ['success', $message];
        }

        public static function warning(string $message): void
        {
            self::$out[] = ['warning', $message];
        }

        /**
         * Exits with code 1, like WP-CLI.
         */
        public static function error(string $message): never
        {
            self::$out[] = ['error', $message];

            throw new WpCliHalt($message, 1);
        }

        /**
         * @return list<string>
         */
        public static function messages(string $type): array
        {
            return array_values(array_map(
                static fn (array $line): string => $line[1],
                array_filter(self::$out, static fn (array $line): bool => $line[0] === $type)
            ));
        }
    }
}

namespace WP_CLI\Utils {
    /**
     * Records the items instead of printing them.
     *
     * @param list<array<string, mixed>> $items
     * @param list<string> $fields
     */
    function format_items(string $format, array $items, array $fields): void
    {
        \WP_CLI::$out[] = ['items', json_encode(['format' => $format, 'items' => $items, 'fields' => $fields], JSON_THROW_ON_ERROR)];
    }
}
