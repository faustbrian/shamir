<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Shamir\Conductors;

use Cline\Shamir\ShamirManager;
use Cline\Shamir\ShareCollection;
use InvalidArgumentException;

use function throw_if;

/**
 * Fluent conductor for configuring and executing secret split operations.
 *
 * Provides a chainable API for setting split parameters before executing
 * the split operation. Immutable - each method returns a new instance.
 *
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
final readonly class SplitConductor
{
    /**
     * Create a new split conductor.
     *
     * @param ShamirManager $manager   Manager instance for executing operations
     * @param string        $secret    The secret to split
     * @param null|int      $threshold Minimum shares needed to reconstruct
     * @param null|int      $shares    Total number of shares to generate
     */
    public function __construct(
        private ShamirManager $manager,
        private string $secret,
        private ?int $threshold = null,
        private ?int $shares = null,
    ) {}

    /**
     * Set the threshold (minimum shares needed to reconstruct).
     *
     * @param int $threshold Number of shares required (k)
     *
     * @return self New conductor instance with threshold set
     */
    public function threshold(int $threshold): self
    {
        return new self($this->manager, $this->secret, $threshold, $this->shares);
    }

    /**
     * Set the total number of shares to generate.
     *
     * @param int $shares Total shares to create (n)
     *
     * @return self New conductor instance with shares set
     */
    public function shares(int $shares): self
    {
        return new self($this->manager, $this->secret, $this->threshold, $shares);
    }

    /**
     * Execute the split operation.
     *
     * Generates shares using the configured parameters. Threshold and shares
     * must be set before calling this method.
     *
     * @throws InvalidArgumentException If threshold > shares or threshold < 2
     * @throws InvalidArgumentException If threshold or shares not set
     *
     * @return ShareCollection Collection of generated shares
     */
    public function split(): ShareCollection
    {
        throw_if($this->threshold === null || $this->shares === null, InvalidArgumentException::class, 'Both threshold and shares must be set before splitting');

        return $this->manager->split($this->secret, $this->threshold, $this->shares);
    }
}
