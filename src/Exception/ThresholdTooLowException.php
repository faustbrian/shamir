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
 * Exception thrown when the threshold is set below the minimum required value.
 *
 * Shamir's Secret Sharing requires a minimum threshold of 2 to provide meaningful
 * security and redundancy. A threshold of 1 would be equivalent to simply storing
 * the secret itself, defeating the purpose of secret sharing. This exception
 * enforces the constraint that threshold ≥ 2 for all split operations.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class ThresholdTooLowException extends InvalidArgumentException implements ShamirException
{
    /**
     * Creates a new exception instance indicating threshold is below minimum.
     *
     * The error message specifies the minimum threshold value of 2, guiding
     * users to configure a valid threshold that provides actual secret sharing
     * security properties.
     *
     * @return self A new exception instance with minimum threshold requirement message
     */
    public static function create(): self
    {
        return new self('Threshold must be at least 2');
    }
}
