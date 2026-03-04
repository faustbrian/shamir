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
 * Base exception for all share compatibility errors.
 *
 * Abstract parent class for exceptions related to share incompatibility issues
 * during the combine operation. Shares can be incompatible for various reasons
 * such as mismatched checksums, different prime configurations, or shares from
 * different split operations.
 *
 * This abstract class serves as a common parent for specific compatibility
 * exceptions, allowing consumers to catch all compatibility-related errors
 * with a single catch block while still providing specific exception types
 * for different failure modes.
 *
 * Concrete subclasses provide specific context about why shares are incompatible,
 * such as checksum mismatches or configuration differences.
 *
 * @author Brian Faust <brian@cline.sh>
 */
abstract class IncompatibleSharesException extends RuntimeException implements ShamirException
{
    // Abstract base - concrete subclasses provide specific factory methods
}
