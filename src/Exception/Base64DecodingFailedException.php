<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Shamir\Exception;

use RuntimeException;

/**
 * Exception thrown when base64 decoding fails.
 * @author Brian Faust <brian@cline.sh>
 */
final class Base64DecodingFailedException extends RuntimeException implements ShamirException
{
    public static function create(): self
    {
        return new self('Failed to decode base64 string');
    }
}
