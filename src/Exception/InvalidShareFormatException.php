<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Shamir\Exception;

use function sprintf;

/**
 * Exception thrown when an encoded share string has an invalid or unrecognized format.
 *
 * Indicates that a share string does not conform to the expected encoding format
 * for Shamir's Secret Sharing. Share strings must follow a specific structure
 * that includes delimiters, properly encoded fields, and valid encoding schemes
 * (base64, hex, etc.) to be successfully parsed back into Share objects.
 *
 * This exception is thrown during share parsing when the encoded string format
 * is malformed, contains invalid characters, has incorrect delimiters, or does
 * not match any recognized share encoding pattern. Common causes include manual
 * string modification, transmission corruption, or using incompatible encoding
 * methods.
 *
 * The exception message includes the malformed encoded string to aid in debugging,
 * allowing developers to identify exactly which share string failed validation.
 *
 * @author Brian Faust <brian@cline.sh>
 *
 * @see InvalidShareDataFormatException For validation of internal share data structure
 */
final class InvalidShareFormatException extends InvalidShareException
{
    /**
     * Create a new invalid share format exception from an encoded share string.
     *
     * Factory method for creating an instance with the malformed encoded share
     * string included in the error message. This helps developers identify which
     * specific share string failed format validation, making debugging easier
     * when processing multiple shares or handling user input.
     *
     * @param string $encoded The malformed encoded share string that failed
     *                        format validation, included in error message for
     *                        diagnostic purposes
     *
     * @return self New exception instance with detailed error message including
     *              the invalid encoded string
     */
    public static function fromEncoded(string $encoded): self
    {
        return new self(sprintf('Invalid share format: %s', $encoded));
    }
}
