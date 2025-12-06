<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Shamir\Exception;

/**
 * Exception thrown when shares have different thresholds.
 * @author Brian Faust <brian@cline.sh>
 */
final class SharesDifferentThresholdsException extends IncompatibleSharesException
{
    public static function create(): self
    {
        return new self('Shares have different thresholds - they are from different split operations');
    }
}
