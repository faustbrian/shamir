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
 * Exception thrown when base64 decoding fails due to invalid input.
 *
 * Indicates that a base64-encoded string could not be decoded, typically because
 * it contains invalid characters or is malformed. This exception is thrown by
 * Base64Encoder when strict mode validation detects non-base64 characters or
 * incorrect padding in the encoded data.
 *
 * Common causes include corrupted shares, manual editing of share strings, or
 * transmission errors that modified the encoded data.
 *
 * @author Brian Faust <brian@cline.sh>
 *
 * @see \Cline\Shamir\Encoding\Base64Encoder::decode()
 */
final class Base64DecodingFailedException extends RuntimeException implements ShamirException
{
    /**
     * Create a new base64 decoding failure exception.
     *
     * Factory method for creating an instance with a standard error message
     * indicating that base64 decoding failed. Typically thrown when the input
     * string contains characters outside the base64 alphabet (A-Z, a-z, 0-9,
     * +, /, =) or has invalid padding.
     *
     * @return self New exception instance with default error message
     */
    public static function create(): self
    {
        return new self('Failed to decode base64 string');
    }
}
