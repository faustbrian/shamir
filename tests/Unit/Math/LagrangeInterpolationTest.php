<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Shamir\Config;
use Cline\Shamir\Math\GaloisField;
use Cline\Shamir\Math\LagrangeInterpolation;
use Cline\Shamir\Math\Polynomial;

describe('LagrangeInterpolation', function (): void {
    test('reconstructs constant term from polynomial points', function (): void {
        $field = new GaloisField(Config::PRIME_256);

        // Create polynomial f(x) = 42 + 3x + 5x²
        $polynomial = new Polynomial($field, ['42', '3', '5']);

        // Generate points
        $points = [
            ['x' => '1', 'y' => $polynomial->evaluate('1')],
            ['x' => '2', 'y' => $polynomial->evaluate('2')],
            ['x' => '3', 'y' => $polynomial->evaluate('3')],
        ];

        // Reconstruct constant term
        $interpolation = new LagrangeInterpolation($field);
        $secret = $interpolation->interpolate($points);

        expect($secret)->toBe('42');
    });

    test('works with minimum points (2 for degree 1)', function (): void {
        $field = new GaloisField(Config::PRIME_256);

        // f(x) = 100 + 7x (degree 1, needs 2 points)
        $polynomial = new Polynomial($field, ['100', '7']);

        $points = [
            ['x' => '1', 'y' => $polynomial->evaluate('1')],
            ['x' => '2', 'y' => $polynomial->evaluate('2')],
        ];

        $interpolation = new LagrangeInterpolation($field);
        $secret = $interpolation->interpolate($points);

        expect($secret)->toBe('100');
    });

    test('works with more points than needed', function (): void {
        $field = new GaloisField(Config::PRIME_256);

        // Degree 2 polynomial (needs 3 points)
        $polynomial = new Polynomial($field, ['99', '2', '4']);

        // Use 5 points (more than needed)
        $points = [
            ['x' => '1', 'y' => $polynomial->evaluate('1')],
            ['x' => '2', 'y' => $polynomial->evaluate('2')],
            ['x' => '3', 'y' => $polynomial->evaluate('3')],
            ['x' => '4', 'y' => $polynomial->evaluate('4')],
            ['x' => '5', 'y' => $polynomial->evaluate('5')],
        ];

        $interpolation = new LagrangeInterpolation($field);
        $secret = $interpolation->interpolate($points);

        expect($secret)->toBe('99');
    });

    test('works with large secret numbers', function (): void {
        $field = new GaloisField(Config::PRIME_256);

        $largeSecret = '1234567890123456789012345678901234567890';
        $polynomial = new Polynomial($field, [$largeSecret, '5', '10']);

        $points = [
            ['x' => '10', 'y' => $polynomial->evaluate('10')],
            ['x' => '20', 'y' => $polynomial->evaluate('20')],
            ['x' => '30', 'y' => $polynomial->evaluate('30')],
        ];

        $interpolation = new LagrangeInterpolation($field);
        $secret = $interpolation->interpolate($points);

        expect($secret)->toBe($largeSecret);
    });
});
