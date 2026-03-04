<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Shamir\Exception\InvalidShareException;
use Cline\Shamir\Share;

describe('Share', function (): void {
    test('creates share with all properties', function (): void {
        $share = new Share(1, 'test-value', 3, 'test-checksum');

        expect($share->getIndex())->toBe(1)
            ->and($share->getValue())->toBe('test-value')
            ->and($share->getThreshold())->toBe(3)
            ->and($share->getChecksum())->toBe('test-checksum');
    });

    test('serializes to string format', function (): void {
        $share = new Share(1, 'abc123', 3, 'checksum');

        expect($share->toString())->toBe('1:3:checksum:abc123');
    });

    test('parses from string format', function (): void {
        $encoded = '2:4:checksum123:value456';
        $share = Share::fromString($encoded);

        expect($share->getIndex())->toBe(2)
            ->and($share->getThreshold())->toBe(4)
            ->and($share->getChecksum())->toBe('checksum123')
            ->and($share->getValue())->toBe('value456');
    });

    test('survives string serialization roundtrip', function (): void {
        $original = new Share(5, 'complex:value:with:colons', 3, 'abc123');
        $serialized = $original->toString();
        $deserialized = Share::fromString($serialized);

        expect($deserialized->getIndex())->toBe($original->getIndex())
            ->and($deserialized->getValue())->toBe($original->getValue())
            ->and($deserialized->getThreshold())->toBe($original->getThreshold())
            ->and($deserialized->getChecksum())->toBe($original->getChecksum());
    });

    test('serializes to JSON', function (): void {
        $share = new Share(1, 'value', 3, 'checksum');
        $json = json_encode($share, \JSON_THROW_ON_ERROR);
        $data = json_decode($json, true, 512, \JSON_THROW_ON_ERROR);

        expect($json)->toBeJson()
            ->and($data)->toHaveKey('index')
            ->toHaveKey('value')
            ->toHaveKey('threshold')
            ->toHaveKey('checksum');
    });

    test('deserializes from array', function (): void {
        $data = [
            'index' => 2,
            'value' => 'test-value',
            'threshold' => 4,
            'checksum' => 'test-checksum',
        ];

        $share = Share::fromArray($data);

        expect($share->getIndex())->toBe(2)
            ->and($share->getValue())->toBe('test-value')
            ->and($share->getThreshold())->toBe(4)
            ->and($share->getChecksum())->toBe('test-checksum');
    });

    test('survives JSON roundtrip', function (): void {
        $original = new Share(3, 'value123', 5, 'checksum456');
        $json = json_encode($original, \JSON_THROW_ON_ERROR);
        $data = json_decode($json, true, 512, \JSON_THROW_ON_ERROR);
        $deserialized = Share::fromArray($data);

        expect($deserialized->getIndex())->toBe($original->getIndex())
            ->and($deserialized->getValue())->toBe($original->getValue())
            ->and($deserialized->getThreshold())->toBe($original->getThreshold())
            ->and($deserialized->getChecksum())->toBe($original->getChecksum());
    });

    test('throws on invalid string format', function (): void {
        Share::fromString('invalid-format');
    })->throws(InvalidShareException::class);

    test('throws on missing array fields', function (): void {
        Share::fromArray(['index' => 1]);
    })->throws(InvalidShareException::class);

    test('implements Stringable', function (): void {
        $share = new Share(1, 'value', 3, 'checksum');

        expect((string) $share)->toBe($share->toString());
    });
});
