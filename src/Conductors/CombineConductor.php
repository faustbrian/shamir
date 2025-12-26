<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Shamir\Conductors;

use Cline\Shamir\Exception\IncompatibleSharesException;
use Cline\Shamir\Exception\InsufficientSharesException;
use Cline\Shamir\Exception\InvalidShareException;
use Cline\Shamir\ShamirManager;
use Cline\Shamir\Share;

/**
 * Fluent conductor for configuring and executing secret combine operations.
 *
 * Provides a chainable API for working with shares before executing the combine
 * operation. This conductor follows an immutable design pattern where each method
 * returns a new instance, allowing for safe method chaining without side effects.
 *
 * The conductor acts as a wrapper around ShamirManager's combine functionality,
 * providing a more fluent and expressive interface for reconstructing secrets from
 * shares using Shamir's Secret Sharing scheme.
 *
 * ```php
 * $secret = Shamir::combine()
 *     ->shares([$share1, $share2, $share3])
 *     ->combine();
 * ```
 *
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 *
 * @see ShamirManager::combine()
 */
final readonly class CombineConductor
{
    /**
     * Create a new combine conductor instance.
     *
     * @param ShamirManager          $manager The Shamir manager instance responsible for executing
     *                                        the actual combine operation. Handles share validation,
     *                                        decoding, and Lagrange interpolation for secret reconstruction.
     * @param iterable<Share|string> $shares  Collection of shares to combine, either as Share objects
     *                                        or encoded string representations. Must contain at least
     *                                        the threshold number of shares from the same split operation.
     */
    public function __construct(
        private ShamirManager $manager,
        private iterable $shares,
    ) {}

    /**
     * Execute the combine operation to reconstruct the original secret.
     *
     * Reconstructs the original secret from the provided shares using Lagrange
     * interpolation over the configured finite field. The shares are validated
     * to ensure they are from the same split operation (matching checksums) and
     * that enough shares are provided to meet the threshold requirement.
     *
     * The reconstruction process:
     * 1. Validates share count meets threshold
     * 2. Verifies all shares are from the same split (checksum compatibility)
     * 3. Decodes share data using the configured encoder
     * 4. Applies Lagrange interpolation to recover secret polynomial
     * 5. Evaluates polynomial at x=0 to recover the original secret
     *
     * @throws IncompatibleSharesException If shares are from different split operations
     *                                     (mismatched checksums indicate incompatibility)
     * @throws InsufficientSharesException If fewer shares provided than the threshold
     *                                     requires for reconstruction
     * @throws InvalidShareException       If any share has an invalid format or cannot
     *                                     be decoded properly
     *
     * @return string The reconstructed secret in its original binary form
     */
    public function combine(): string
    {
        return $this->manager->combine($this->shares);
    }
}
