<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Shamir\Exception;

use RuntimeException;

use function sprintf;

/**
 * Exception thrown when a secret is too large to be processed with the configured field size.
 *
 * Indicates that the secret data exceeds the maximum size that can be safely
 * processed using the current finite field configuration. Shamir's Secret Sharing
 * operates over a finite field defined by a prime modulus, which limits the
 * maximum value that can be represented in a single chunk of data.
 *
 * When secrets are split, they are chunked into pieces that fit within the field
 * size. If the secret is larger than supported, it must either be split into
 * smaller chunks or a larger field size must be used. This exception prevents
 * data loss or corruption that would occur from attempting to process oversized
 * secrets.
 *
 * The exception message includes both the actual secret size and the maximum
 * field size to help developers understand the constraint violation and determine
 * whether to reduce the secret size or increase the field size configuration.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class SecretTooLargeException extends RuntimeException implements ShamirException
{
    /**
     * Create a new secret too large exception with size information.
     *
     * Factory method for creating an instance with detailed information about
     * the size constraint violation. Includes both the actual secret size and
     * the maximum allowed field size in the error message, enabling developers
     * to make informed decisions about chunking strategy or field configuration.
     *
     * @param int $secretSize The actual size of the secret in bytes that was
     *                        provided for processing
     * @param int $maxSize    The maximum allowed field size in bytes for the
     *                        current finite field configuration
     *
     * @return self New exception instance with detailed error message including
     *              both the actual and maximum sizes
     */
    public static function exceedsFieldSize(int $secretSize, int $maxSize): self
    {
        return new self(sprintf(
            'Secret size (%d bytes) exceeds maximum field size (%d bytes)',
            $secretSize,
            $maxSize,
        ));
    }
}
