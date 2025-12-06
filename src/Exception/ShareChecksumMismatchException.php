<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Shamir\Exception;

/**
 * Exception thrown when share checksum verification fails.
 * @author Brian Faust <brian@cline.sh>
 */
final class ShareChecksumMismatchException extends InvalidShareException
{
    public static function create(): self
    {
        return new self('Share checksum verification failed - data may be corrupted');
    }
}
