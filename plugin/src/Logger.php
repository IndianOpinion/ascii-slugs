<?php

declare(strict_types=1);

namespace IndianOpinion\AsciiSlugs\WordPress;

use Throwable;

/**
 * One-line log entries, off unless `ascii_slugs_log` returns true.
 *
 * Lines never hold the raw title: titles may be unpublished news. They
 * hold the method, the coverage and at most 40 characters of the slug.
 */
final class Logger
{
    private const PREFIX = 'ascii-slugs: ';

    /** @var callable(string): mixed */
    private $writer;

    /**
     * @param callable(string): mixed|null $writer Defaults to error_log().
     */
    public function __construct(?callable $writer = null)
    {
        $this->writer = $writer ?? static function (string $line): bool {
            return error_log($line);
        };
    }

    public function enabled(): bool
    {
        $default = defined('WP_DEBUG_LOG') && (bool) constant('WP_DEBUG_LOG');
        $value = apply_filters('ascii_slugs_log', $default);

        return is_bool($value) ? $value : $default;
    }

    public function generated(string $method, float $coverage, string $slug): void
    {
        $this->write(sprintf('method=%s coverage=%.2f slug=%s', $method, $coverage, substr($slug, 0, 40)));
    }

    public function error(Throwable $error): void
    {
        $message = preg_replace('/[^\x20-\x7E]/', '?', $error->getMessage()) ?? '';
        $this->write(sprintf('error=%s message=%s', get_class($error), substr($message, 0, 200)));
    }

    /**
     * A notice about configuration, for example a filter that returned the
     * wrong type. The message must not contain a title.
     */
    public function notice(string $message): void
    {
        $this->write('notice=' . $message);
    }

    private function write(string $line): void
    {
        if ($this->enabled()) {
            ($this->writer)(self::PREFIX . $line);
        }
    }
}
