<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Shamir\Exception;

/**
 * Exception thrown when shares have different checksums during reconstruction.
 *
 * All shares generated from a single split operation share the same checksum
 * value, which is derived from the original secret and threshold configuration.
 * This exception is raised when attempting to combine shares with mismatched
 * checksums, indicating they originated from different split operations and
 * cannot be used together to reconstruct a coherent secret.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class SharesDifferentChecksumsException extends IncompatibleSharesException
{
    /**
     * Creates a new exception instance indicating checksum incompatibility.
     *
     * The error message alerts users that the provided shares have different
     * checksum values and likely come from separate split operations, making
     * them unsuitable for combining into a single secret reconstruction.
     *
     * @return self A new exception instance with a descriptive incompatibility message
     */
    public static function create(): self
    {
        return new self('Shares have incompatible checksums - they may not be from the same split operation');
    }
}
