<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Shamir\Exception;

/**
 * Exception thrown when share checksum verification fails during reconstruction.
 *
 * This exception indicates that a share's checksum does not match its expected
 * value, suggesting potential data corruption during transmission or storage.
 * The checksum is computed from the share's data and threshold value to ensure
 * integrity across the entire secret sharing operation.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class ShareChecksumMismatchException extends InvalidShareException
{
    /**
     * Creates a new exception instance indicating checksum verification failure.
     *
     * This factory method produces a descriptive error message alerting users
     * that the share data may be corrupted and cannot be trusted for secret
     * reconstruction.
     *
     * @return self A new exception instance with a descriptive error message
     */
    public static function create(): self
    {
        return new self('Share checksum verification failed - data may be corrupted');
    }
}
