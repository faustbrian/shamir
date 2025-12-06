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
 * Base exception for all invalid share validation errors.
 *
 * Abstract parent class for exceptions related to share validation failures
 * during parsing, decoding, or verification operations. Shares can be invalid
 * for various reasons including malformed encoding, missing required fields,
 * checksum mismatches, or structural inconsistencies.
 *
 * This abstract class serves as a common parent for specific share validation
 * exceptions, allowing consumers to catch all share-related validation errors
 * with a single catch block while still providing specific exception types
 * for different validation failure modes.
 *
 * Concrete subclasses provide specific context about why a share is invalid,
 * such as format errors, missing fields, or checksum failures. This exception
 * hierarchy helps distinguish share validation errors from other runtime errors
 * and share incompatibility issues.
 *
 * @author Brian Faust <brian@cline.sh>
 *
 * @see ShareNotFoundException For shares that cannot be located by index
 * @see ShareMissingRequiredFieldsException For shares missing critical data
 * @see ShareChecksumMismatchException For shares with checksum validation failures
 * @see InvalidShareFormatException For shares with invalid encoded format
 */
abstract class InvalidShareException extends RuntimeException implements ShamirException
{
    // Abstract base - concrete subclasses provide specific factory methods
}
