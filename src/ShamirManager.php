<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Shamir;

use Cline\Shamir\Conductors\CombineConductor;
use Cline\Shamir\Conductors\SplitConductor;
use InvalidArgumentException;

use function array_all;
use function count;

/**
 * Central manager for Shamir's Secret Sharing operations.
 *
 * Orchestrates secret splitting and combining operations while maintaining
 * configuration state. Provides both direct API methods and fluent conductor
 * interfaces for flexible usage patterns.
 *
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
final readonly class ShamirManager
{
    /**
     * Create a new Shamir manager instance.
     *
     * @param Config $config Configuration for field size and encoding
     */
    public function __construct(
        private Config $config = new Config(),
    ) {}

    /**
     * Split a secret into shares.
     *
     * Divides a secret into N shares where any K shares can reconstruct
     * the original secret. Uses polynomial interpolation over finite fields
     * with automatic chunking for large secrets.
     *
     * @param string $secret    The secret to split (any binary string)
     * @param int    $threshold Minimum shares needed to reconstruct (k)
     * @param int    $shares    Total number of shares to generate (n)
     *
     * @throws InvalidArgumentException If threshold > shares or threshold < 2
     *
     * @return ShareCollection Collection of generated shares
     */
    public function split(string $secret, int $threshold, int $shares): ShareCollection
    {
        $splitter = new SecretSplitter($this->config);

        return $splitter->split($secret, $threshold, $shares);
    }

    /**
     * Reconstruct a secret from shares.
     *
     * Combines shares using Lagrange interpolation to recover the original
     * secret. Validates checksums and compatibility before reconstruction.
     *
     * @param iterable<Share|string> $shares Shares to combine
     *
     * @throws Exception\IncompatibleSharesException If shares are from different splits
     * @throws Exception\InsufficientSharesException If fewer shares than threshold
     * @throws Exception\InvalidShareException       If share format is invalid
     *
     * @return string The reconstructed secret
     */
    public function combine(iterable $shares): string
    {
        $combiner = new SecretCombiner($this->config);

        return $combiner->combine($shares);
    }

    /**
     * Verify shares are compatible (from same split operation).
     *
     * Checks that all shares have matching threshold values, indicating
     * they were generated from the same split operation.
     *
     * @param Share ...$shares Variable number of shares to verify
     *
     * @return bool True if all shares are compatible
     */
    public function areCompatible(Share ...$shares): bool
    {
        if (count($shares) < 2) {
            return true;
        }

        $firstThreshold = $shares[0]->getThreshold();

        return array_all($shares, fn ($share): bool => $share->getThreshold() === $firstThreshold);
    }

    /**
     * Create a new manager instance with different configuration.
     *
     * Returns a new manager with the specified configuration, useful for
     * working with different field sizes or encoding formats.
     *
     * @param Config $config New configuration to use
     *
     * @return self New manager instance
     */
    public function withConfig(Config $config): self
    {
        return new self($config);
    }

    /**
     * Begin a fluent chain to split a secret.
     *
     * Creates a conductor for configuring and executing a split operation
     * using method chaining.
     *
     * ```php
     * $shares = $manager->for($secret)
     *     ->threshold(3)
     *     ->shares(5)
     *     ->split();
     * ```
     *
     * @param string $secret The secret to split
     *
     * @return SplitConductor Fluent conductor for split configuration
     */
    public function for(string $secret): SplitConductor
    {
        return new SplitConductor($this, $secret);
    }

    /**
     * Begin a fluent chain to combine shares.
     *
     * Creates a conductor for configuring and executing a combine operation
     * using method chaining.
     *
     * ```php
     * $secret = $manager->from($shares)->combine();
     * ```
     *
     * @param iterable<Share|string> $shares Shares to combine
     *
     * @return CombineConductor Fluent conductor for combine configuration
     */
    public function from(iterable $shares): CombineConductor
    {
        return new CombineConductor($this, $shares);
    }

    /**
     * Get the current configuration.
     *
     * @return Config Active configuration instance
     */
    public function getConfig(): Config
    {
        return $this->config;
    }
}
