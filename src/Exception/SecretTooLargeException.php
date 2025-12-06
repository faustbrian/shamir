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
final class SecretTooLargeException extends RuntimeException implements ShamirException
{
    public static function exceedsFieldSize(int $secretSize, int $maxSize): self
    {
        return new self(sprintf(
            'Secret size (%d bytes) exceeds maximum field size (%d bytes)',
            $secretSize,
            $maxSize,
        ));
    }
}
