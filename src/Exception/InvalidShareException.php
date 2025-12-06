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
 * Base exception for all invalid share errors.
 * @author Brian Faust <brian@cline.sh>
 */
abstract class InvalidShareException extends RuntimeException implements ShamirException
{
    // Abstract base - no factory methods
}
