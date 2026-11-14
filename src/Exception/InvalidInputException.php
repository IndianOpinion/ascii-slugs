<?php

declare(strict_types=1);

namespace IndianOpinion\AsciiSlugs\Exception;

use InvalidArgumentException;

/**
 * The input text is not valid UTF-8.
 */
final class InvalidInputException extends InvalidArgumentException implements AsciiSlugsException
{
}
