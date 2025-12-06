<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Shamir;

use Cline\Shamir\Exception\InvalidShareFormatException;
use Cline\Shamir\Exception\ShareMissingRequiredFieldsException;

use function count;
use function explode;
use function is_int;
use function is_numeric;
use function is_string;
use function sprintf;

/**
 * Service for serializing and deserializing Share objects.
 *
 * Handles all serialization formats including string, JSON, and array
 * representations. Keeps Share as a pure value object by extracting
 * all serialization logic into this service.
 *
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
final readonly class ShareSerializer
{
    /**
     * Serialize a share to portable string format.
     *
     * Format: "index:threshold:checksum:value"
     *
     * @param Share $share The share to serialize
     *
     * @return string Serialized share string
     */
    public function toString(Share $share): string
    {
        return sprintf(
            '%d:%d:%s:%s',
            $share->getIndex(),
            $share->getThreshold(),
            $share->getChecksum(),
            $share->getValue(),
        );
    }

    /**
     * Parse a share from string format.
     *
     * Expects format: "index:threshold:checksum:value"
     *
     * @param string $encoded Serialized share string
     *
     * @throws InvalidShareFormatException If format is invalid
     *
     * @return Share Deserialized share object
     */
    public function fromString(string $encoded): Share
    {
        $parts = explode(':', $encoded, 4);

        if (count($parts) !== 4) {
            throw InvalidShareFormatException::fromEncoded($encoded);
        }

        [$index, $threshold, $checksum, $value] = $parts;

        if (!is_numeric($index) || !is_numeric($threshold)) {
            throw InvalidShareFormatException::fromEncoded($encoded);
        }

        return new Share(
            (int) $index,
            $value,
            (int) $threshold,
            $checksum,
        );
    }

    /**
     * Serialize a share to array format.
     *
     * Useful for JSON encoding or database storage.
     *
     * @param Share $share The share to serialize
     *
     * @return array{index: int, value: string, threshold: int, checksum: string}
     */
    public function toArray(Share $share): array
    {
        return [
            'index' => $share->getIndex(),
            'value' => $share->getValue(),
            'threshold' => $share->getThreshold(),
            'checksum' => $share->getChecksum(),
        ];
    }

    /**
     * Parse a share from array format.
     *
     * Expects array with keys: index, value, threshold, checksum
     *
     * @param array<string, mixed> $data Array representation of share
     *
     * @throws ShareMissingRequiredFieldsException If required fields are missing or invalid
     *
     * @return Share Deserialized share object
     */
    public function fromArray(array $data): Share
    {
        if (
            !isset($data['index'], $data['value'], $data['threshold'], $data['checksum'])
            || !is_int($data['index'])
            || !is_string($data['value'])
            || !is_int($data['threshold'])
            || !is_string($data['checksum'])
        ) {
            throw ShareMissingRequiredFieldsException::create();
        }

        return new Share(
            $data['index'],
            $data['value'],
            $data['threshold'],
            $data['checksum'],
        );
    }
}
