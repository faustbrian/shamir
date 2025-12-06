<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Shamir\Math;

/**
 * Lagrange interpolation for polynomial reconstruction.
 * Given k points (xᵢ, yᵢ), reconstructs the unique polynomial of degree k-1.
 * @psalm-immutable
 * @author Brian Faust <brian@cline.sh>
 */
final readonly class LagrangeInterpolation
{
    public function __construct(
        private GaloisField $field,
    ) {}

    /**
     * Interpolate polynomial from points and evaluate at x=0 to get constant term.
     *
     * @param array<int, array{x: string, y: string}> $points Array of [x, y] points
     *
     * @return string The constant term (secret)
     */
    public function interpolate(array $points): string
    {
        $secret = '0';

        foreach ($points as $i => $pointI) {
            $xi = $pointI['x'];
            $yi = $pointI['y'];

            $numerator = '1';
            $denominator = '1';

            foreach ($points as $j => $pointJ) {
                if ($i === $j) {
                    continue;
                }

                $xj = $pointJ['x'];

                // Numerator: (0 - xⱼ) = -xⱼ
                $numerator = $this->field->multiply(
                    $numerator,
                    $this->field->subtract('0', $xj),
                );

                // Denominator: (xᵢ - xⱼ)
                $denominator = $this->field->multiply(
                    $denominator,
                    $this->field->subtract($xi, $xj),
                );
            }

            // Basis polynomial: numerator / denominator
            $basis = $this->field->divide($numerator, $denominator);

            // Add yᵢ * basis to result
            $term = $this->field->multiply($yi, $basis);
            $secret = $this->field->add($secret, $term);
        }

        return $secret;
    }
}
