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
 * Exception thrown when threshold exceeds total shares.
 * @author Brian Faust <brian@cline.sh>
 */
final class ThresholdExceedsSharesException extends InvalidArgumentException implements ShamirException
{
    public static function create(): self
    {
        return new self('Threshold cannot be greater than total shares');
    }
}
