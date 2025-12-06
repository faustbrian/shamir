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
 * Polynomial operations in a Galois Field.
 * Represents polynomials of the form: f(x) = a₀ + a₁x + a₂x² + ... + aₙxⁿ
 * @psalm-immutable
 * @author Brian Faust <brian@cline.sh>
 */
final readonly class Polynomial
{
    /**
     * @param array<int, string> $coefficients Coefficients [a₀, a₁, ..., aₙ] (constant term first)
     */
    public function __construct(
        private GaloisField $field,
        private array $coefficients,
    ) {}

    /**
     * Create a random polynomial with given degree and constant term.
     *
     * @throws Exception
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
     * f(x) = a₀ + x(a₁ + x(a₂ + x(...)))
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
     * Get the degree of the polynomial (highest power).
     */
    public function degree(): int
    {
        return count($this->coefficients) - 1;
    }

    /**
     * Get the constant term (coefficient of x⁰).
     */
    public function getConstantTerm(): string
    {
        return $this->coefficients[0] ?? '0';
    }

    /**
     * Get all coefficients.
     *
     * @return array<int, string>
     */
    public function getCoefficients(): array
    {
        return $this->coefficients;
    }
}
