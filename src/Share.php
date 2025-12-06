<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Shamir;

use JsonSerializable;
use Stringable;

/**
 * Pure value object representing a single share in Shamir's Secret Sharing scheme.
 *
 * Contains only data and accessors. All serialization logic has been extracted
 * to ShareSerializer service to maintain separation of concerns.
 *
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
final readonly class Share implements JsonSerializable, Stringable
{
    /**
     * Create a new share instance.
     *
     * @param int    $index     Share index (1-based)
     * @param string $value     Encoded share value
     * @param int    $threshold Minimum shares needed to reconstruct
     * @param string $checksum  SHA-256 checksum for validation
     */
    public function __construct(
        private int $index,
        private string $value,
        private int $threshold,
        private string $checksum,
    ) {}

    /**
     * String representation for type casting.
     *
     * @return string Serialized share string
     */
    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * Parse from string format.
     *
     * Delegates to ShareSerializer for actual deserialization logic.
     *
     * @param string $encoded Serialized share string
     *
     * @throws Exception\InvalidShareException If format is invalid
     *
     * @return self Deserialized share object
     */
    public static function fromString(string $encoded): self
    {
        $serializer = new ShareSerializer();

        return $serializer->fromString($encoded);
    }

    /**
     * Create from array (JSON deserialization).
     *
     * Delegates to ShareSerializer for actual deserialization logic.
     *
     * @param array<string, mixed> $data Array representation
     *
     * @throws Exception\InvalidShareException If required fields are missing
     *
     * @return self Deserialized share object
     */
    public static function fromArray(array $data): self
    {
        $serializer = new ShareSerializer();

        return $serializer->fromArray($data);
    }

    /**
     * Get the share's index.
     *
     * @return int Share index (1-based)
     */
    public function getIndex(): int
    {
        return $this->index;
    }

    /**
     * Get the encoded share value.
     *
     * @return string Encoded share value
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Get the threshold required to reconstruct.
     *
     * @return int Minimum number of shares needed
     */
    public function getThreshold(): int
    {
        return $this->threshold;
    }

    /**
     * Get the share's checksum.
     *
     * @return string SHA-256 checksum
     */
    public function getChecksum(): string
    {
        return $this->checksum;
    }

    /**
     * Serialize to portable string format.
     *
     * Delegates to ShareSerializer for actual serialization logic.
     * Format: "index:threshold:checksum:value"
     *
     * @return string Serialized share string
     */
    public function toString(): string
    {
        $serializer = new ShareSerializer();

        return $serializer->toString($this);
    }

    /**
     * Serialize to array for JSON encoding.
     *
     * Delegates to ShareSerializer for actual serialization logic.
     *
     * @return array{index: int, value: string, threshold: int, checksum: string}
     */
    public function jsonSerialize(): array
    {
        $serializer = new ShareSerializer();

        return $serializer->toArray($this);
    }
}
