<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Shamir\Math;

use Exception;

use function bin2hex;
use function count;
use function gmp_init;
use function gmp_strval;
use function random_bytes;

/**
 * Polynomial operations in a Galois Field for Shamir's Secret Sharing.
 *
 * Represents polynomials of the form: f(x) = a₀ + a₁x + a₂x² + ... + aₙxⁿ
 * where all coefficients and operations are performed in a finite field.
 * The constant term (a₀) represents the secret in Shamir's scheme, while
 * evaluations at different points generate shares.
 *
 * @psalm-immutable
 * @author Brian Faust <brian@cline.sh>
 */
final readonly class Polynomial
{
    /**
     * Create a new polynomial in a Galois Field.
     *
     * @param GaloisField        $field        The finite field for arithmetic operations
     * @param array<int, string> $coefficients Polynomial coefficients [a₀, a₁, ..., aₙ] as numeric
     *                                         strings, ordered from constant term (a₀) to highest
     *                                         degree term. All coefficients must be valid field elements.
     */
    public function __construct(
        private GaloisField $field,
        private array $coefficients,
    ) {}

    /**
     * Create a random polynomial with given degree and constant term.
     *
     * Generates a polynomial where the constant term is fixed (the secret)
     * and all higher-degree coefficients are randomly generated. This is used
     * in Shamir's Secret Sharing to create shares where any k shares can
     * reconstruct the secret through Lagrange interpolation.
     *
     * @param GaloisField $field        The finite field for operations
     * @param int         $degree       Degree of polynomial (threshold - 1)
     * @param string      $constantTerm The secret value as constant term (a₀)
     *
     * @throws Exception If random_bytes() fails to generate secure random data
     *
     * @return self Random polynomial with specified properties
     */
    public static function random(
        GaloisField $field,
        int $degree,
        string $constantTerm,
    ): self {
        $coefficients = [$constantTerm];

        for ($i = 1; $i <= $degree; ++$i) {
            // Generate random coefficient as numeric string
            $randomBytes = random_bytes(16);
            $coefficients[] = gmp_strval(gmp_init(bin2hex($randomBytes), 16));
        }

        return new self($field, $coefficients);
    }

    /**
     * Evaluate polynomial at point x using Horner's method.
     *
     * Computes f(x) = a₀ + x(a₁ + x(a₂ + x(...))) efficiently in O(n) time.
     * Horner's method minimizes the number of field multiplications required
     * and provides numerical stability for polynomial evaluation.
     *
     * @param string $x The point at which to evaluate, as numeric string
     *
     * @return string The polynomial value f(x) as numeric string
     */
    public function evaluate(string $x): string
    {
        if ($this->coefficients === []) {
            return '0';
        }

        $result = $this->coefficients[count($this->coefficients) - 1];

        for ($i = count($this->coefficients) - 2; $i >= 0; --$i) {
            $result = $this->field->multiply($result, $x);
            $result = $this->field->add($result, $this->coefficients[$i]);
        }

        return $result;
    }

    /**
     * Get the degree of the polynomial (highest power of x).
     *
     * The degree equals the number of coefficients minus one. A polynomial
     * with n+1 coefficients has degree n. In Shamir's scheme, the degree
     * equals threshold - 1.
     *
     * @return int Polynomial degree (0 for constants, n for degree-n polynomials)
     */
    public function degree(): int
    {
        return count($this->coefficients) - 1;
    }

    /**
     * Get the constant term (coefficient of x⁰).
     *
     * In Shamir's Secret Sharing, the constant term represents the secret
     * value that is being split across shares.
     *
     * @return string The constant term a₀ as numeric string
     */
    public function getConstantTerm(): string
    {
        return $this->coefficients[0] ?? '0';
    }

    /**
     * Get all polynomial coefficients.
     *
     * Returns coefficients in order from constant term (a₀) to highest
     * degree term. All coefficients are represented as numeric strings
     * for arbitrary precision arithmetic in the Galois Field.
     *
     * @return array<int, string> Coefficients array [a₀, a₁, ..., aₙ]
     */
    public function getCoefficients(): array
    {
        return $this->coefficients;
    }
}
