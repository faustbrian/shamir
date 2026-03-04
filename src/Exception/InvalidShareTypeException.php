<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Shamir\Exception;

use InvalidArgumentException;

/**
 * Exception thrown when a share has an invalid or unsupported PHP type.
 *
 * Indicates that a value provided as a share does not match the expected type
 * constraints for Shamir's Secret Sharing operations. Share processing methods
 * accept only specific types: Share objects for direct use, or strings for
 * parsing encoded shares. Other types cannot be safely processed.
 *
 * This exception is thrown during type validation when a share parameter receives
 * an incompatible type such as integers, arrays, objects of wrong classes, or
 * null values. It ensures type safety throughout the share processing pipeline
 * and prevents undefined behavior from attempting to process unsupported types.
 *
 * The solution is to either provide Share objects directly or encode shares as
 * strings using the appropriate encoding format (base64, hex, etc.).
 *
 * @author Brian Faust <brian@cline.sh>
 *
 * @see \Cline\Shamir\Share For the expected Share object type
 */
final class InvalidShareTypeException extends InvalidArgumentException implements ShamirException
{
    /**
     * Create a new invalid share type exception.
     *
     * Factory method for creating an instance with a standard error message
     * indicating that shares must be either Share objects or properly encoded
     * strings. This provides clear guidance on the acceptable types, helping
     * developers understand what they need to provide.
     *
     * @return self New exception instance with default error message
     */
    public static function create(): self
    {
        return new self('Shares must be Share objects or strings');
    }
}
