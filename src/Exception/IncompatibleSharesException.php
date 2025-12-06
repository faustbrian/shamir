<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Shamir\Exception;

final class IncompatibleSharesException extends ShamirException
{
    public static function differentThresholds(): self
    {
        return new self('Shares have different thresholds - they are from different split operations');
    }

    public static function differentChecksums(): self
    {
        return new self('Shares have incompatible checksums - they may not be from the same split operation');
    }
}
