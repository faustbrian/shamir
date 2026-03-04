<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Shamir\Exception\InsufficientSharesException;
use Cline\Shamir\Shamir;

describe('Shamir', function (): void {
    test('splits and combines secret with exact threshold', function (): void {
        $secret = 'test-secret';
        $shares = Shamir::split($secret, threshold: 3, shares: 5);

        expect($shares)->toHaveCount(5);
        expect(Shamir::combine($shares->take(3)))->toBe($secret);
    });

    test('fails with fewer than threshold shares', function (): void {
        $shares = Shamir::split('secret', 3, 5);

        Shamir::combine($shares->take(2));
    })->throws(InsufficientSharesException::class);

    test('any combination of threshold shares works', function (): void {
        $secret = 'test-secret';
        $shares = Shamir::split($secret, 3, 5)->toArray();

        // Test all 10 combinations of 3 shares from 5
        $combinations = combinations($shares, 3);

        foreach ($combinations as $combo) {
            expect(Shamir::combine($combo))->toBe($secret);
        }
    });

    test('handles binary secrets', function (): void {
        $secret = hex2bin('0123456789abcdef0123456789abcdef0123456789abcdef0123456789abcdef');
        $shares = Shamir::split($secret, 3, 5);

        expect(Shamir::combine($shares->take(3)))->toBe($secret);
    });

    test('handles secrets larger than field size', function (): void {
        $secret = str_repeat('x', 1_000);
        $shares = Shamir::split($secret, 3, 5);

        expect(Shamir::combine($shares->take(3)))->toBe($secret);
    });

    test('handles empty secret', function (): void {
        $secret = '';
        $shares = Shamir::split($secret, 3, 5);

        expect(Shamir::combine($shares->take(3)))->toBe($secret);
    });

    test('minimum threshold of 2', function (): void {
        $shares = Shamir::split('secret', 2, 3);

        expect(Shamir::combine($shares->take(2)))->toBe('secret');
    });

    test('threshold equals shares', function (): void {
        $shares = Shamir::split('secret', 5, 5);

        expect(Shamir::combine($shares))->toBe('secret');
    });

    test('rejects threshold of 1', function (): void {
        Shamir::split('secret', 1, 5);
    })->throws(InvalidArgumentException::class);

    test('rejects threshold greater than shares', function (): void {
        Shamir::split('secret', 5, 3);
    })->throws(InvalidArgumentException::class);

    test('shares reveal nothing individually', function (): void {
        $shares1 = Shamir::split('secret-a', 3, 5);
        $shares2 = Shamir::split('secret-b', 3, 5);

        // Individual shares should look random, no correlation
        expect($shares1->get(1)->getValue())
            ->not->toBe($shares2->get(1)->getValue());
    });

    test('compatible shares have same threshold', function (): void {
        $shares = Shamir::split('secret', 3, 5)->toArray();

        expect(Shamir::areCompatible($shares[0], $shares[1], $shares[2]))->toBeTrue();
    });

    test('incompatible shares have different thresholds', function (): void {
        $shares1 = Shamir::split('secret1', 2, 3)->toArray();
        $shares2 = Shamir::split('secret2', 3, 5)->toArray();

        expect(Shamir::areCompatible($shares1[0], $shares2[0]))->toBeFalse();
    });
});
