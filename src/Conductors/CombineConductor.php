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
 * Provides a chainable API for working with shares before executing
 * the combine operation. Immutable - each method returns a new instance.
 *
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
final readonly class CombineConductor
{
    /**
     * Create a new combine conductor.
     *
     * @param ShamirManager          $manager Manager instance for executing operations
     * @param iterable<Share|string> $shares  Shares to combine
     */
    public function __construct(
        private ShamirManager $manager,
        private iterable $shares,
    ) {}

    /**
     * Execute the combine operation.
     *
     * Reconstructs the original secret from the provided shares using
     * Lagrange interpolation.
     *
     * @throws IncompatibleSharesException If shares are from different splits
     * @throws InsufficientSharesException If fewer shares than threshold
     * @throws InvalidShareException       If share format is invalid
     *
     * @return string The reconstructed secret
     */
    public function combine(): string
    {
        return $this->manager->combine($this->shares);
    }
}
