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
 * Exception thrown when chunk data has an invalid or unexpected type during processing.
 *
 * Indicates that data being processed as a chunk does not match the expected
 * type requirements for Shamir's Secret Sharing operations. Chunks must be
 * valid byte sequences or numeric values that can be processed through the
 * finite field arithmetic used in share generation and secret reconstruction.
 *
 * This exception is thrown when type validation fails during chunk processing,
 * ensuring that only properly typed data flows through the splitting and
 * combining algorithms. Common causes include passing non-numeric types where
 * GMP integers are expected, or providing incompatible data structures.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class InvalidChunkDataTypeException extends RuntimeException implements ShamirException
{
    /**
     * Create a new invalid chunk data type exception.
     *
     * Factory method for creating an instance with a standard error message
     * indicating that the chunk data type is invalid. Signals that data
     * validation detected an incompatible type that cannot be processed
     * by the cryptographic operations.
     *
     * @return self New exception instance with default error message
     */
    public static function create(): self
    {
        return new self('Invalid chunk data type');
    }
}
