<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Shamir\Math;

/**
 * Lagrange polynomial interpolation for Shamir's Secret Sharing reconstruction.
 *
 * Implements Lagrange basis polynomial interpolation over a finite field to
 * reconstruct a polynomial from a set of points. Given k points (xᵢ, yᵢ),
 * this algorithm uniquely determines the polynomial of degree k-1 that passes
 * through all points. The secret is recovered by evaluating this polynomial at x=0.
 *
 * The Lagrange interpolation formula computes:
 * P(x) = Σᵢ yᵢ × Lᵢ(x), where Lᵢ(x) = Πⱼ≠ᵢ (x - xⱼ) / (xᵢ - xⱼ)
 *
 * All arithmetic is performed in the provided Galois Field to maintain the
 * security properties required for Shamir's Secret Sharing.
 *
 * @psalm-immutable This class is immutable and all methods are pure functions
 *
 * @author Brian Faust <brian@cline.sh>
 */
final readonly class LagrangeInterpolation
{
    /**
     * Creates a new Lagrange interpolation instance.
     *
     * @param GaloisField $field The finite field over which polynomial arithmetic
     *                           is performed. All interpolation calculations use
     *                           this field's modular arithmetic operations.
     */
    public function __construct(
        private GaloisField $field,
    ) {}

    /**
     * Interpolates a polynomial from points and evaluates at x=0 to recover the secret.
     *
     * Uses Lagrange basis polynomials to reconstruct the unique polynomial of
     * degree k-1 passing through k points, then evaluates it at x=0 to obtain
     * the constant term (the original secret). Each point represents a share
     * with x as the share index and y as the share value.
     *
     * The algorithm constructs Lagrange basis polynomials for each point and
     * combines them to recover the original polynomial's constant term without
     * explicitly computing the full polynomial coefficients.
     *
     * ```php
     * $field = new GaloisField('2305843009213693951');
     * $lagrange = new LagrangeInterpolation($field);
     *
     * $points = [
     *     ['x' => '1', 'y' => '1234...'],
     *     ['x' => '2', 'y' => '5678...'],
     *     ['x' => '3', 'y' => '9012...'],
     * ];
     *
     * $secret = $lagrange->interpolate($points);
     * ```
     *
     * @param array<int, array{x: string, y: string}> $points Array of coordinate pairs where each
     *                                                        element contains 'x' (share index) and
     *                                                        'y' (share value) as arbitrary precision
     *                                                        strings. Must contain at least threshold
     *                                                        number of distinct points.
     *
     * @return string The reconstructed secret (polynomial constant term) as a string
     */
    public function interpolate(array $points): string
    {
        // Initialize accumulator for the secret (polynomial value at x=0)
        $secret = '0';

        // Compute each Lagrange basis polynomial Lᵢ(0) and combine with yᵢ
        foreach ($points as $i => $pointI) {
            $xi = $pointI['x'];
            $yi = $pointI['y'];

            // Initialize numerator and denominator for this basis polynomial
            $numerator = '1';
            $denominator = '1';

            // Build the basis polynomial Lᵢ(0) = Πⱼ≠ᵢ (0 - xⱼ) / (xᵢ - xⱼ)
            foreach ($points as $j => $pointJ) {
                if ($i === $j) {
                    continue;
                }

                $xj = $pointJ['x'];

                // Numerator: accumulate (0 - xⱼ) = -xⱼ for all j ≠ i
                $numerator = $this->field->multiply(
                    $numerator,
                    $this->field->subtract('0', $xj),
                );

                // Denominator: accumulate (xᵢ - xⱼ) for all j ≠ i
                $denominator = $this->field->multiply(
                    $denominator,
                    $this->field->subtract($xi, $xj),
                );
            }

            // Compute Lagrange basis polynomial: Lᵢ(0) = numerator / denominator
            $basis = $this->field->divide($numerator, $denominator);

            // Add weighted basis: yᵢ × Lᵢ(0) to the accumulating secret
            $term = $this->field->multiply($yi, $basis);
            $secret = $this->field->add($secret, $term);
        }

        return $secret;
    }
}
