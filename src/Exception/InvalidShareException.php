<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Shamir\Exception;

use function sprintf;

final class InvalidShareException extends ShamirException
{
    public static function invalidFormat(string $encoded): self
    {
        return new self(sprintf('Invalid share format: %s', $encoded));
    }

    public static function missingRequiredFields(): self
    {
        return new self('Share is missing required fields: index, value, threshold, checksum');
    }

    public static function shareNotFound(int $index): self
    {
        return new self(sprintf('Share with index %d not found', $index));
    }

    public static function checksumMismatch(): self
    {
        return new self('Share checksum verification failed - data may be corrupted');
    }
}
