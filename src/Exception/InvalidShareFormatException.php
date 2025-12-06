<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Shamir\Exception;

use function sprintf;

/**
 * Exception thrown when a share has an invalid format.
 * @author Brian Faust <brian@cline.sh>
 */
final class InvalidShareFormatException extends InvalidShareException
{
    public static function fromEncoded(string $encoded): self
    {
        return new self(sprintf('Invalid share format: %s', $encoded));
    }
}
