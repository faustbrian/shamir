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
 * Exception thrown when a share with a specific index is not found.
 * @author Brian Faust <brian@cline.sh>
 */
final class ShareNotFoundException extends InvalidShareException
{
    public static function withIndex(int $index): self
    {
        return new self(sprintf('Share with index %d not found', $index));
    }
}
