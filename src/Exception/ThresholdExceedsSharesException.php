<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Shamir\Exception;

use InvalidArgumentException;

/**
 * Exception thrown when the threshold exceeds the total number of shares.
 *
 * In Shamir's Secret Sharing, the threshold represents the minimum number of
 * shares required to reconstruct the secret. This value cannot logically exceed
 * the total number of shares being generated, as it would make reconstruction
 * impossible. This exception enforces the constraint that threshold ≤ total shares.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class ThresholdExceedsSharesException extends InvalidArgumentException implements ShamirException
{
    /**
     * Creates a new exception instance indicating invalid threshold configuration.
     *
     * The error message explicitly states that the threshold cannot exceed the
     * total shares count, guiding users to configure valid split parameters.
     *
     * @return self A new exception instance with threshold constraint message
     */
    public static function create(): self
    {
        return new self('Threshold cannot be greater than total shares');
    }
}
