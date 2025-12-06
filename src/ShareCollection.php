<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Shamir;

use ArrayIterator;
use Cline\Shamir\Exception\InsufficientSharesInCollectionException;
use Cline\Shamir\Exception\ShareNotFoundException;
use Countable;
use Exception;
use IteratorAggregate;
use JsonSerializable;
use Traversable;

use function array_map;
use function array_rand;
use function array_slice;
use function array_values;
use function count;
use function is_array;
use function shuffle;
use function throw_if;

/**
 * Collection of shares with utility methods.
 *
 * @psalm-immutable
 * @author Brian Faust <brian@cline.sh>
 * @implements IteratorAggregate<int, Share>
 */
final readonly class ShareCollection implements Countable, IteratorAggregate, JsonSerializable
{
    /**
     * @param array<Share> $shares
     */
    public function __construct(
        private array $shares,
    ) {}

    /**
     * Get share by index (1-based).
     *
     * @throws ShareNotFoundException
     */
    public function get(int $index): Share
    {
        foreach ($this->shares as $share) {
            if ($share->getIndex() === $index) {
                return $share;
            }
        }

        throw ShareNotFoundException::withIndex($index);
    }

    /**
     * Take first N shares.
     */
    public function take(int $count): self
    {
        return new self(array_slice($this->shares, 0, $count));
    }

    /**
     * Get N random shares.
     *
     * @throws Exception
     */
    public function random(int $count): self
    {
        throw_if($count > count($this->shares), InsufficientSharesInCollectionException::create());

        $keys = array_rand($this->shares, $count);

        if (!is_array($keys)) {
            $keys = [$keys];
        }

        $randomShares = [];

        foreach ($keys as $key) {
            $randomShares[] = $this->shares[$key];
        }

        return new self($randomShares);
    }

    public function count(): int
    {
        return count($this->shares);
    }

    /**
     * @return array<Share>
     */
    public function toArray(): array
    {
        return $this->shares;
    }

    /**
     * @return Traversable<int, Share>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->shares);
    }

    /**
     * @return list<array{index: int, value: string, threshold: int, checksum: string}>
     */
    public function jsonSerialize(): array
    {
        return array_values(array_map(
            static fn (Share $share): array => $share->jsonSerialize(),
            $this->shares,
        ));
    }

    /**
     * Get shares suitable for distribution (randomized order).
     *
     * @throws Exception
     * @return array<int, Share> Indexed by share index
     */
    public function forDistribution(): array
    {
        $shares = $this->shares;
        shuffle($shares);

        $distribution = [];

        foreach ($shares as $share) {
            $distribution[$share->getIndex()] = $share;
        }

        return $distribution;
    }
}
