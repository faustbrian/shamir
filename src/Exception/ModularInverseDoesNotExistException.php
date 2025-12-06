<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Shamir\Exception;

use RuntimeException;

/**
 * Exception thrown when modular inverse does not exist.
 * @author Brian Faust <brian@cline.sh>
 */
final class ModularInverseDoesNotExistException extends RuntimeException implements ShamirException
{
    public static function create(): self
    {
        return new self('Modular inverse does not exist');
    }
}
