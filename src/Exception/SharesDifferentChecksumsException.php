<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Shamir\Exception;

/**
 * Exception thrown when shares have incompatible checksums.
 * @author Brian Faust <brian@cline.sh>
 */
final class SharesDifferentChecksumsException extends IncompatibleSharesException
{
    public static function create(): self
    {
        return new self('Shares have incompatible checksums - they may not be from the same split operation');
    }
}
