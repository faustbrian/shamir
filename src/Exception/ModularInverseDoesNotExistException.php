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
 * Exception thrown when a modular multiplicative inverse cannot be computed.
 *
 * Indicates that the extended Euclidean algorithm failed to find a modular
 * multiplicative inverse for a given number and modulus. A modular inverse
 * exists only when the number and modulus are coprime (their greatest common
 * divisor is 1). This is a fundamental requirement for the finite field
 * arithmetic used in Shamir's Secret Sharing.
 *
 * This exception is thrown during Lagrange interpolation when computing
 * coefficients for secret reconstruction. It signals a critical mathematical
 * constraint violation that prevents the combine operation from succeeding.
 * This typically indicates corrupted share data, non-prime modulus values,
 * or duplicate x-coordinates in the polynomial evaluation points.
 *
 * In a properly functioning Shamir implementation using a prime modulus, this
 * exception should never occur with valid shares. Its presence indicates either
 * data corruption or a programming error in share generation or processing.
 *
 * @author Brian Faust <brian@cline.sh>
 *
 * @internal This exception indicates a serious mathematical constraint violation
 */
final class ModularInverseDoesNotExistException extends RuntimeException implements ShamirException
{
    /**
     * Create a new modular inverse does not exist exception.
     *
     * Factory method for creating an instance with a standard error message
     * indicating that a modular multiplicative inverse could not be computed.
     * This signals a fundamental mathematical constraint violation in the
     * finite field arithmetic used for secret reconstruction.
     *
     * @return self New exception instance with default error message
     */
    public static function create(): self
    {
        return new self('Modular inverse does not exist');
    }
}
