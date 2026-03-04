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
 * Collection of shares with utility methods for manipulation and distribution.
 *
 * Provides a type-safe container for Share objects with methods for retrieval,
 * sampling, and serialization. Implements standard PHP collection interfaces
 * for seamless integration with language features and libraries.
 *
 * @psalm-immutable
 * @author Brian Faust <brian@cline.sh>
 * @implements IteratorAggregate<int, Share>
 */
final readonly class ShareCollection implements Countable, IteratorAggregate, JsonSerializable
{
    /**
     * Create a new share collection.
     *
     * @param array<Share> $shares Array of Share objects to collect. The array is stored
     *                             as-is without validation or reindexing, preserving the
     *                             original structure for iteration and access operations.
     */
    public function __construct(
        private array $shares,
    ) {}

    /**
     * Get share by index (1-based).
     *
     * Searches the collection for a share with the specified index value.
     * Share indices are 1-based, matching the mathematical notation used
     * in Shamir's Secret Sharing scheme.
     *
     * @param int $index The share index to find (1-based)
     *
     * @throws ShareNotFoundException If no share with the given index exists
     *
     * @return Share The share with the requested index
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
     * Take first N shares from the collection.
     *
     * Returns a new collection containing only the first N shares in the
     * current collection order. Useful for testing minimum threshold scenarios
     * or when you want a deterministic subset of shares.
     *
     * @param int $count Number of shares to take from the beginning
     *
     * @return self New collection with first N shares
     */
    public function take(int $count): self
    {
        return new self(array_slice($this->shares, 0, $count));
    }

    /**
     * Get N random shares from the collection.
     *
     * Returns a new collection containing a random selection of shares.
     * This is useful for simulating distributed scenarios where only a
     * subset of shares are available, or for testing reconstruction with
     * different share combinations.
     *
     * @param int $count Number of shares to randomly select
     *
     * @throws Exception                               If array_rand() fails
     * @throws InsufficientSharesInCollectionException If count exceeds available shares
     *
     * @return self New collection with randomly selected shares
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

    /**
     * Get the number of shares in the collection.
     *
     * Implements Countable interface to allow use of count() function.
     *
     * @return int Number of shares in collection
     */
    public function count(): int
    {
        return count($this->shares);
    }

    /**
     * Get all shares as a plain array.
     *
     * Returns the internal shares array without modification. Useful for
     * passing to functions that expect arrays rather than collection objects.
     *
     * @return array<Share> Array of all Share objects
     */
    public function toArray(): array
    {
        return $this->shares;
    }

    /**
     * Get iterator for foreach loops.
     *
     * Implements IteratorAggregate interface to make the collection iterable.
     * Allows direct use in foreach statements and other iterator-based operations.
     *
     * @return Traversable<int, Share> Iterator over shares
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->shares);
    }

    /**
     * Serialize collection to JSON-compatible array.
     *
     * Implements JsonSerializable interface for automatic JSON encoding.
     * Each share is serialized to its array representation using Share::jsonSerialize().
     *
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
     * Returns shares in a shuffled order, indexed by their share index for
     * easy identification. The randomization ensures that sequential distribution
     * doesn't create predictable patterns, improving security in distributed
     * scenarios where share distribution order might be observable.
     *
     * @throws Exception If shuffle() fails (extremely rare)
     *
     * @return array<int, Share> Shares indexed by their share index (1-based)
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
