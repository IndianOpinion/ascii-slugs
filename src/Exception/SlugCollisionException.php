<?php

declare(strict_types=1);

namespace IndianOpinion\AsciiSlugs\Exception;

use RuntimeException;

/**
 * Every candidate slug tried by the uniqueness helper was taken.
 */
final class SlugCollisionException extends RuntimeException implements AsciiSlugsException
{
}
