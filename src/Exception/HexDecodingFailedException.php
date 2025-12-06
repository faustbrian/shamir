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
 * Exception thrown when hexadecimal decoding fails due to invalid input.
 *
 * Indicates that a hex-encoded string could not be decoded, typically because
 * it contains invalid characters, has odd length, or is otherwise malformed.
 * This exception is thrown by HexEncoder when validation detects non-hex
 * characters or structural issues in the encoded data.
 *
 * Common causes include corrupted shares, manual editing of share strings,
 * transmission errors, or truncation that resulted in an odd-length string
 * (hex encoding always produces even-length output).
 *
 * @author Brian Faust <brian@cline.sh>
 *
 * @see \Cline\Shamir\Encoding\HexEncoder::decode()
 */
final class HexDecodingFailedException extends RuntimeException implements ShamirException
{
    /**
     * Create a new hex decoding failure exception.
     *
     * Factory method for creating an instance with a standard error message
     * indicating that hex decoding failed. Typically thrown when the input
     * string contains characters outside the hex alphabet (0-9, a-f, A-F)
     * or has an odd length (valid hex strings always have even length).
     *
     * @return self New exception instance with default error message
     */
    public static function create(): self
    {
        return new self('Failed to decode hex string');
    }
}
