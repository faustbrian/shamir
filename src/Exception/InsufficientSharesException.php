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
 * Exception thrown when fewer shares are provided than the threshold requires.
 *
 * Indicates that a combine operation cannot proceed because the number of shares
 * provided is less than the threshold (k) required to reconstruct the secret.
 * Shamir's Secret Sharing requires at least k shares to successfully recover
 * the original secret using Lagrange interpolation.
 *
 * This exception includes the number of shares provided and the threshold
 * requirement in the error message to help diagnose the issue. The solution
 * is to provide additional valid shares from the same split operation until
 * the threshold is met.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class InsufficientSharesException extends RuntimeException implements ShamirException
{
    /**
     * Create a new insufficient shares exception with specific counts.
     *
     * Factory method for creating an instance with a detailed error message
     * indicating how many shares were provided versus how many are required.
     * This helps users understand exactly how many more shares they need to
     * provide to successfully reconstruct the secret.
     *
     * @param int $provided The number of shares that were actually provided
     *                      for the combine operation
     * @param int $required The minimum number of shares (threshold) needed
     *                      to reconstruct the secret
     *
     * @return self New exception instance with detailed error message including
     *              both the provided and required share counts
     */
    public static function notEnoughShares(int $provided, int $required): self
    {
        return new self(sprintf(
            'Insufficient shares provided. Need %d, got %d',
            $required,
            $provided,
        ));
    }
}
