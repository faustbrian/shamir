<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Shamir\Math;

use Cline\Shamir\Exception\ModularInverseDoesNotExistException;
use GMP;

use function gmp_add;
use function gmp_init;
use function gmp_invert;
use function gmp_mod;
use function gmp_mul;
use function gmp_strval;
use function gmp_sub;
use function throw_if;

/**
 * Finite field arithmetic implementation over GF(p) for Shamir's Secret Sharing.
 *
 * Provides mathematical operations in a Galois Field (finite field) modulo a large
 * prime number. All arithmetic operations (addition, subtraction, multiplication,
 * division) are performed modulo the prime, ensuring results remain within the field.
 * This implementation is essential for Shamir's Secret Sharing polynomial arithmetic,
 * where operations must be performed in a finite field to maintain security properties.
 *
 * The field is characterized by a prime modulus p, where all operations produce
 * results in the range [0, p-1]. Division is implemented as multiplication by the
 * modular multiplicative inverse.
 *
 * @psalm-immutable This class is immutable and all methods are pure functions
 *
 * @author Brian Faust <brian@cline.sh>
 */
final readonly class GaloisField
{
    /**
     * The prime modulus defining the finite field.
     *
     * @var GMP Large prime number used as the modulus for all field operations
     */
    private GMP $prime;

    /**
     * Creates a new Galois Field with the specified prime modulus.
     *
     * Initializes a finite field GF(p) where p is the provided prime number.
     * All subsequent arithmetic operations will be performed modulo this prime.
     *
     * @param string $prime Large prime number as a string to support arbitrary precision
     */
    public function __construct(string $prime)
    {
        $this->prime = gmp_init($prime);
    }

    /**
     * Adds two numbers in the finite field.
     *
     * Performs modular addition: (a + b) mod p. The result is automatically
     * reduced modulo the field's prime to ensure it remains within [0, p-1].
     *
     * @param string $a First operand as a string for arbitrary precision arithmetic
     * @param string $b Second operand as a string for arbitrary precision arithmetic
     *
     * @return string The sum (a + b) mod p as a string
     */
    public function add(string $a, string $b): string
    {
        $result = gmp_add(gmp_init($a), gmp_init($b));

        return gmp_strval(gmp_mod($result, $this->prime));
    }

    /**
     * Subtracts two numbers in the finite field.
     *
     * Performs modular subtraction: (a - b) mod p. The result is automatically
     * reduced modulo the field's prime, handling negative results correctly by
     * wrapping them into the valid range [0, p-1].
     *
     * @param string $a Minuend as a string for arbitrary precision arithmetic
     * @param string $b Subtrahend as a string for arbitrary precision arithmetic
     *
     * @return string The difference (a - b) mod p as a string
     */
    public function subtract(string $a, string $b): string
    {
        $result = gmp_sub(gmp_init($a), gmp_init($b));

        return gmp_strval(gmp_mod($result, $this->prime));
    }

    /**
     * Multiplies two numbers in the finite field.
     *
     * Performs modular multiplication: (a × b) mod p. The result is automatically
     * reduced modulo the field's prime to ensure it remains within [0, p-1].
     *
     * @param string $a First multiplicand as a string for arbitrary precision arithmetic
     * @param string $b Second multiplicand as a string for arbitrary precision arithmetic
     *
     * @return string The product (a × b) mod p as a string
     */
    public function multiply(string $a, string $b): string
    {
        $result = gmp_mul(gmp_init($a), gmp_init($b));

        return gmp_strval(gmp_mod($result, $this->prime));
    }

    /**
     * Divides two numbers in the finite field.
     *
     * Performs modular division by computing a × b⁻¹ mod p, where b⁻¹ is the
     * modular multiplicative inverse of b. Division in finite fields is defined
     * as multiplication by the inverse rather than traditional division.
     *
     * @param string $a Dividend as a string for arbitrary precision arithmetic
     * @param string $b Divisor as a string for arbitrary precision arithmetic
     *
     * @throws ModularInverseDoesNotExistException When b has no modular inverse (b ≡ 0 mod p or gcd(b,p) ≠ 1)
     * @return string                              The quotient (a × b⁻¹) mod p as a string
     */
    public function divide(string $a, string $b): string
    {
        $bInv = $this->modInverse($b);

        return $this->multiply($a, $bInv);
    }

    /**
     * Computes the modular multiplicative inverse of a number.
     *
     * Finds a value a⁻¹ such that (a × a⁻¹) ≡ 1 (mod p) using the Extended
     * Euclidean Algorithm. The inverse exists if and only if gcd(a, p) = 1.
     * Since p is prime, the inverse exists for all a where 0 < a < p.
     *
     * @param string $a The number to invert as a string for arbitrary precision arithmetic
     *
     * @throws ModularInverseDoesNotExistException When the inverse does not exist (a ≡ 0 mod p or gcd(a,p) ≠ 1)
     * @return string                              The modular inverse a⁻¹ mod p as a string
     */
    public function modInverse(string $a): string
    {
        $inv = gmp_invert(gmp_init($a), $this->prime);

        throw_if($inv === false, ModularInverseDoesNotExistException::create());

        return gmp_strval($inv);
    }

    /**
     * Reduces a number modulo the field's prime.
     *
     * Computes a mod p, ensuring the result is in the valid field range [0, p-1].
     * This operation is implicitly performed by all other field operations but
     * can be called explicitly when needed.
     *
     * @param string $a The number to reduce as a string for arbitrary precision arithmetic
     *
     * @return string The reduced value a mod p as a string
     */
    public function mod(string $a): string
    {
        return gmp_strval(gmp_mod(gmp_init($a), $this->prime));
    }
}
