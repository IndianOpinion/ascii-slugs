<?php

declare(strict_types=1);

namespace IndianOpinion\AsciiSlugs\Exception;

use InvalidArgumentException;

/**
 * An option value is outside its allowed range or has the wrong type.
 */
final class InvalidOptionException extends InvalidArgumentException implements AsciiSlugsException
{
}
