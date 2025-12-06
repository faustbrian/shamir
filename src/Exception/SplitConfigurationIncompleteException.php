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
 * Exception thrown when split configuration is incomplete.
 * @author Brian Faust <brian@cline.sh>
 */
final class SplitConfigurationIncompleteException extends InvalidArgumentException implements ShamirException
{
    public static function create(): self
    {
        return new self('Both threshold and shares must be set before splitting');
    }
}
