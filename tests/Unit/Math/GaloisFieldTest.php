<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Shamir\Config;
use Cline\Shamir\Math\GaloisField;

describe('GaloisField', function (): void {
    test('adds two numbers in the field', function (): void {
        $field = new GaloisField(Config::PRIME_256);

        $result = $field->add('100', '200');

        expect($result)->toBe('300');
    });

    test('subtracts two numbers in the field', function (): void {
        $field = new GaloisField(Config::PRIME_256);

        $result = $field->subtract('200', '100');

        expect($result)->toBe('100');
    });

    test('multiplies two numbers in the field', function (): void {
        $field = new GaloisField(Config::PRIME_256);

        $result = $field->multiply('10', '20');

        expect($result)->toBe('200');
    });

    test('divides two numbers in the field', function (): void {
        $field = new GaloisField(Config::PRIME_256);

        $result = $field->divide('200', '10');

        expect($result)->toBe('20');
    });

    test('computes modular inverse', function (): void {
        $field = new GaloisField(Config::PRIME_256);

        $inverse = $field->modInverse('7');
        $product = $field->multiply('7', $inverse);

        expect($product)->toBe('1');
    });

    test('reduces numbers modulo prime', function (): void {
        $field = new GaloisField('13');

        expect($field->mod('15'))->toBe('2')
            ->and($field->mod('26'))->toBe('0')
            ->and($field->mod('30'))->toBe('4');
    });

    test('handles large numbers with 256-bit prime', function (): void {
        $field = new GaloisField(Config::PRIME_256);

        $a = '123456789012345678901234567890';
        $b = '987654321098765432109876543210';

        $sum = $field->add($a, $b);
        $product = $field->multiply($a, $b);

        expect($sum)->toBeString()
            ->and($product)->toBeString();
    });
});
