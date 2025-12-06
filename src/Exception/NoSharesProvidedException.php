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
 * Exception thrown when an empty share collection is provided for secret reconstruction.
 *
 * Indicates that a combine operation was attempted with zero shares, making
 * secret reconstruction impossible. Shamir's Secret Sharing requires at least
 * the threshold number of shares (k) to reconstruct a secret, so providing
 * no shares at all is an invalid operation that must be detected early.
 *
 * This exception is thrown during input validation before any cryptographic
 * operations begin, preventing unnecessary processing and providing clear
 * feedback about the missing input. It typically indicates a programming error,
 * such as passing an empty array or collection to the combine method.
 *
 * The solution is to provide at least the threshold number of valid shares
 * from the same split operation before attempting secret reconstruction.
 *
 * @author Brian Faust <brian@cline.sh>
 *
 * @see InsufficientSharesException For when shares are provided but fewer than threshold
 */
final class NoSharesProvidedException extends InvalidArgumentException implements ShamirException
{
    /**
     * Create a new no shares provided exception.
     *
     * Factory method for creating an instance with a standard error message
     * indicating that the share collection is empty. Provides clear feedback
     * that shares must be provided before attempting secret reconstruction,
     * helping developers identify missing input early in the process.
     *
     * @return self New exception instance with default error message
     */
    public static function create(): self
    {
        return new self('No shares provided');
    }
}
