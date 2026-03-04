<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Shamir\Exception;

/**
 * Exception thrown when a share is missing one or more required fields.
 *
 * A valid Shamir secret share must contain four essential fields: index
 * (the x-coordinate), value (the y-coordinate), threshold (minimum shares
 * needed for reconstruction), and checksum (integrity verification hash).
 * This exception is thrown when deserialization or validation detects that
 * any of these required fields are absent from the share data structure.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class ShareMissingRequiredFieldsException extends InvalidShareException
{
    /**
     * Creates a new exception instance listing all required share fields.
     *
     * The error message explicitly enumerates the four mandatory fields that
     * must be present in every valid share: index, value, threshold, and checksum.
     *
     * @return self A new exception instance with enumerated required fields
     */
    public static function create(): self
    {
        return new self('Share is missing required fields: index, value, threshold, checksum');
    }
}
