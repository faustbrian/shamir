<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Shamir\Math;

use GMP;
use RuntimeException;

use function gmp_add;
use function gmp_init;
use function gmp_invert;
use function gmp_mod;
use function gmp_mul;
use function gmp_strval;
use function gmp_sub;
use function throw_if;

/**
 * Finite field arithmetic over GF(p).
 * All operations are performed modulo a prime p.
 * @psalm-immutable
 */
final readonly class GaloisField
{
    private GMP $prime;

    public function __construct(string $prime)
    {
        $this->prime = gmp_init($prime);
    }

    /**
     * Add two numbers in the field.
     */
    public function add(string $a, string $b): string
    {
        $result = gmp_add(gmp_init($a), gmp_init($b));

        return gmp_strval(gmp_mod($result, $this->prime));
    }

    /**
     * Subtract two numbers in the field.
     */
    public function subtract(string $a, string $b): string
    {
        $result = gmp_sub(gmp_init($a), gmp_init($b));

        return gmp_strval(gmp_mod($result, $this->prime));
    }

    /**
     * Multiply two numbers in the field.
     */
    public function multiply(string $a, string $b): string
    {
        $result = gmp_mul(gmp_init($a), gmp_init($b));

        return gmp_strval(gmp_mod($result, $this->prime));
    }

    /**
     * Divide two numbers in the field.
     * Division is multiplication by modular inverse.
     */
    public function divide(string $a, string $b): string
    {
        $bInv = $this->modInverse($b);

        return $this->multiply($a, $bInv);
    }

    /**
     * Compute modular inverse of a number.
     * Uses Extended Euclidean Algorithm.
     */
    public function modInverse(string $a): string
    {
        $inv = gmp_invert(gmp_init($a), $this->prime);

        throw_if($inv === false, RuntimeException::class, 'Modular inverse does not exist');

        return gmp_strval($inv);
    }

    /**
     * Reduce a number modulo the prime.
     */
    public function mod(string $a): string
    {
        return gmp_strval(gmp_mod(gmp_init($a), $this->prime));
    }
}
