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
 * Exception thrown when share data structure has an invalid or malformed format.
 *
 * Indicates that the internal data representation of a share does not conform
 * to the expected structure required for Shamir's Secret Sharing operations.
 * Share data must contain properly formatted fields including index, value,
 * threshold, and checksum information in the correct binary or encoded format.
 *
 * This exception is thrown during share parsing or deserialization when the
 * data structure is corrupted, incomplete, or does not match the expected
 * schema. Common causes include binary data corruption, version mismatches,
 * or manual modification of serialized share data.
 *
 * Different from InvalidShareFormatException which validates the external
 * encoded string format, this exception validates the internal data structure
 * after decoding.
 *
 * @author Brian Faust <brian@cline.sh>
 *
 * @see InvalidShareFormatException For validation of encoded share strings
 */
final class InvalidShareDataFormatException extends RuntimeException implements ShamirException
{
    /**
     * Create a new invalid share data format exception.
     *
     * Factory method for creating an instance with a standard error message
     * indicating that the share's internal data format is invalid. Signals
     * that the data structure does not match the expected schema for share
     * objects, preventing successful parsing or reconstruction.
     *
     * @return self New exception instance with default error message
     */
    public static function create(): self
    {
        return new self('Invalid share data format');
    }
}
