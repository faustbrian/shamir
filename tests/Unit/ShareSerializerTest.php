<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Unit;

use Cline\Shamir\Exception\InvalidShareException;
use Cline\Shamir\Share;
use Cline\Shamir\ShareSerializer;

use function describe;
use function expect;
use function test;

describe('ShareSerializer', function (): void {
    test('serializes share to string', function (): void {
        $serializer = new ShareSerializer();
        $share = new Share(1, 'encoded_value', 3, 'checksum123');

        $string = $serializer->toString($share);

        expect($string)->toBe('1:3:checksum123:encoded_value');
    });

    test('deserializes share from string', function (): void {
        $serializer = new ShareSerializer();

        $share = $serializer->fromString('1:3:checksum123:encoded_value');

        expect($share->getIndex())->toBe(1)
            ->and($share->getThreshold())->toBe(3)
            ->and($share->getChecksum())->toBe('checksum123')
            ->and($share->getValue())->toBe('encoded_value');
    });

    test('survives string serialization roundtrip', function (): void {
        $serializer = new ShareSerializer();
        $original = new Share(2, 'test_value', 5, 'abc123');

        $string = $serializer->toString($original);
        $reconstructed = $serializer->fromString($string);

        expect($reconstructed->getIndex())->toBe($original->getIndex())
            ->and($reconstructed->getValue())->toBe($original->getValue())
            ->and($reconstructed->getThreshold())->toBe($original->getThreshold())
            ->and($reconstructed->getChecksum())->toBe($original->getChecksum());
    });

    test('serializes share to array', function (): void {
        $serializer = new ShareSerializer();
        $share = new Share(1, 'value', 3, 'checksum');

        $array = $serializer->toArray($share);

        expect($array)->toBe([
            'index' => 1,
            'value' => 'value',
            'threshold' => 3,
            'checksum' => 'checksum',
        ]);
    });

    test('deserializes share from array', function (): void {
        $serializer = new ShareSerializer();
        $data = [
            'index' => 1,
            'value' => 'value',
            'threshold' => 3,
            'checksum' => 'checksum',
        ];

        $share = $serializer->fromArray($data);

        expect($share->getIndex())->toBe(1)
            ->and($share->getValue())->toBe('value')
            ->and($share->getThreshold())->toBe(3)
            ->and($share->getChecksum())->toBe('checksum');
    });

    test('throws on invalid string format', function (): void {
        $serializer = new ShareSerializer();

        expect(fn (): Share => $serializer->fromString('invalid'))
            ->toThrow(InvalidShareException::class);
    });

    test('throws on missing array fields', function (): void {
        $serializer = new ShareSerializer();

        expect(fn (): Share => $serializer->fromArray(['index' => 1]))
            ->toThrow(InvalidShareException::class);
    });

    test('throws on invalid array field types', function (): void {
        $serializer = new ShareSerializer();
        $data = [
            'index' => '1',  // Should be int
            'value' => 'value',
            'threshold' => 3,
            'checksum' => 'checksum',
        ];

        expect(fn (): Share => $serializer->fromArray($data))
            ->toThrow(InvalidShareException::class);
    });
});
