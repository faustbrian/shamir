<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Shamir\Conductors;

use Cline\Shamir\Exception\SplitConfigurationIncompleteException;
use Cline\Shamir\ShamirManager;
use Cline\Shamir\ShareCollection;
use InvalidArgumentException;

use function throw_if;

/**
 * Fluent conductor for configuring and executing secret split operations.
 *
 * Provides a chainable API for setting split parameters (threshold and total shares)
 * before executing the split operation. This conductor follows an immutable design
 * pattern where each configuration method returns a new instance, allowing for safe
 * method chaining without side effects.
 *
 * The conductor acts as a builder for Shamir's Secret Sharing split operations,
 * requiring both threshold (k) and total shares (n) to be configured before execution.
 *
 * ```php
 * $shares = Shamir::split('my-secret')
 *     ->threshold(3)
 *     ->shares(5)
 *     ->split();
 * ```
 *
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 *
 * @see ShamirManager::split()
 */
final readonly class SplitConductor
{
    /**
     * Create a new split conductor instance.
     *
     * @param ShamirManager $manager   The Shamir manager instance responsible for executing
     *                                 the actual split operation. Handles polynomial generation,
     *                                 share creation, and encoding of share data.
     * @param string        $secret    The secret data to be split into shares. Can be any binary
     *                                 data including strings, serialized objects, or encrypted content.
     * @param null|int      $threshold Minimum number of shares (k) needed to reconstruct the secret.
     *                                 Must be set before calling split(). Value must be ≥ 2 and ≤ shares.
     * @param null|int      $shares    Total number of shares (n) to generate from the secret.
     *                                 Must be set before calling split(). Value must be ≥ threshold.
     */
    public function __construct(
        private ShamirManager $manager,
        private string $secret,
        private ?int $threshold = null,
        private ?int $shares = null,
    ) {}

    /**
     * Set the threshold (minimum shares needed to reconstruct the secret).
     *
     * Configures the minimum number of shares (k) required to reconstruct the
     * original secret. This value determines the degree of the polynomial used
     * in Shamir's Secret Sharing scheme (degree = k - 1).
     *
     * @param int $threshold Number of shares required for reconstruction. Must be
     *                       at least 2 and cannot exceed the total number of shares.
     *                       Higher values increase security but require more shares
     *                       for recovery.
     *
     * @return self New conductor instance with the threshold configured, preserving
     *              immutability while allowing method chaining
     */
    public function threshold(int $threshold): self
    {
        return new self($this->manager, $this->secret, $threshold, $this->shares);
    }

    /**
     * Set the total number of shares to generate.
     *
     * Configures the total number of shares (n) that will be generated from the
     * secret. More shares can be generated than the threshold requires, allowing
     * for redundancy and distribution to multiple parties.
     *
     * @param int $shares Total number of shares to create. Must be at least equal
     *                    to the threshold value. Each share will be a complete,
     *                    independent piece that can be distributed separately.
     *
     * @return self New conductor instance with the shares count configured, preserving
     *              immutability while allowing method chaining
     */
    public function shares(int $shares): self
    {
        return new self($this->manager, $this->secret, $this->threshold, $shares);
    }

    /**
     * Execute the split operation to generate shares from the secret.
     *
     * Generates the configured number of shares using Shamir's Secret Sharing scheme.
     * Both threshold and shares must be set before calling this method, otherwise
     * an exception is thrown.
     *
     * The split process:
     * 1. Validates threshold and shares are configured
     * 2. Chunks the secret into manageable pieces
     * 3. Generates random polynomial coefficients for each chunk
     * 4. Evaluates polynomials at distinct x-coordinates for each share
     * 5. Encodes share data using the configured encoder
     * 6. Returns collection of encoded shares with checksums
     *
     * @throws InvalidArgumentException              If threshold > shares or threshold < 2,
     *                                               indicating invalid configuration
     * @throws SplitConfigurationIncompleteException If threshold or shares not set
     *                                               before calling split()
     *
     * @return ShareCollection Collection of generated shares, each containing encoded
     *                         share data, checksum, and metadata for reconstruction
     */
    public function split(): ShareCollection
    {
        throw_if($this->threshold === null || $this->shares === null, SplitConfigurationIncompleteException::create());

        return $this->manager->split($this->secret, $this->threshold, $this->shares);
    }
}
