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
 * Exception thrown when trying to take more shares than available in collection.
 * @author Brian Faust <brian@cline.sh>
 */
final class InsufficientSharesInCollectionException extends InvalidArgumentException implements ShamirException
{
    public static function create(): self
    {
        return new self('Cannot take more shares than available');
    }
}
