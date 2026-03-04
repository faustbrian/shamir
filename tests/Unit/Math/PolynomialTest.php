<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Shamir\Config;
use Cline\Shamir\Math\GaloisField;
use Cline\Shamir\Math\Polynomial;

describe('Polynomial', function (): void {
    test('evaluates polynomial at given point', function (): void {
        $field = new GaloisField(Config::PRIME_256);

        // f(x) = 5 + 3x + 2x²
        $polynomial = new Polynomial($field, ['5', '3', '2']);

        // f(2) = 5 + 3(2) + 2(2²) = 5 + 6 + 8 = 19
        expect($polynomial->evaluate('2'))->toBe('19');
    });

    test('evaluates constant polynomial', function (): void {
        $field = new GaloisField(Config::PRIME_256);
        $polynomial = new Polynomial($field, ['42']);

        expect($polynomial->evaluate('100'))->toBe('42');
    });

    test('gets polynomial degree', function (): void {
        $field = new GaloisField(Config::PRIME_256);

        $polynomial = new Polynomial($field, ['1', '2', '3', '4']);

        expect($polynomial->degree())->toBe(3);
    });

    test('gets constant term', function (): void {
        $field = new GaloisField(Config::PRIME_256);
        $polynomial = new Polynomial($field, ['42', '100', '200']);

        expect($polynomial->getConstantTerm())->toBe('42');
    });

    test('creates random polynomial with correct degree', function (): void {
        $field = new GaloisField(Config::PRIME_256);

        $polynomial = Polynomial::random($field, degree: 3, constantTerm: '123');

        expect($polynomial->degree())->toBe(3)
            ->and($polynomial->getConstantTerm())->toBe('123');
    });

    test('random polynomials are different', function (): void {
        $field = new GaloisField(Config::PRIME_256);

        $poly1 = Polynomial::random($field, 2, '100');
        $poly2 = Polynomial::random($field, 2, '100');

        // Same constant term but different random coefficients
        expect($poly1->getConstantTerm())->toBe($poly2->getConstantTerm())
            ->and($poly1->evaluate('5'))->not->toBe($poly2->evaluate('5'));
    });
});
