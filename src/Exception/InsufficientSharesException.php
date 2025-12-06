<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Shamir\Exception;

use RuntimeException;

use function sprintf;

/**
 * @author Brian Faust <brian@cline.sh>
 */
final class InsufficientSharesException extends RuntimeException implements ShamirException
{
    public static function notEnoughShares(int $provided, int $required): self
    {
        return new self(sprintf(
            'Insufficient shares provided. Need %d, got %d',
            $required,
            $provided,
        ));
    }
}
