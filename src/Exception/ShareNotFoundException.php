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
 * Exception thrown when a share with a specific index cannot be located.
 *
 * During secret reconstruction, shares are accessed by their index values
 * (x-coordinates in the polynomial). This exception is raised when attempting
 * to retrieve a share with a specific index that does not exist in the
 * available shares collection. This may occur during validation, lookup
 * operations, or when processing combine requests.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class ShareNotFoundException extends InvalidShareException
{
    /**
     * Creates a new exception instance for a missing share index.
     *
     * The error message includes the specific index value that could not be
     * found, enabling precise debugging and error reporting for share lookup
     * failures.
     *
     * @param int $index The share index (x-coordinate) that was not found in the collection
     *
     * @return self A new exception instance with the missing index in the error message
     */
    public static function withIndex(int $index): self
    {
        return new self(sprintf('Share with index %d not found', $index));
    }
}
