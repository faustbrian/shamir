<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Shamir\Exception;

use InvalidArgumentException;

/**
 * Exception thrown when attempting to take more shares than available in a share collection.
 *
 * Indicates that a take operation on a ShareCollection requested more shares
 * than currently exist in the collection. This validation prevents invalid
 * operations that would attempt to retrieve non-existent shares, ensuring
 * collection integrity and preventing undefined behavior.
 *
 * Typically thrown by ShareCollection::take() when the requested count exceeds
 * the collection size. The solution is to either request fewer shares or add
 * more shares to the collection before performing the take operation.
 *
 * @author Brian Faust <brian@cline.sh>
 *
 * @see \Cline\Shamir\ShareCollection::take()
 */
final class InsufficientSharesInCollectionException extends InvalidArgumentException implements ShamirException
{
    /**
     * Create a new insufficient shares in collection exception.
     *
     * Factory method for creating an instance with a standard error message
     * indicating that the take operation requested more shares than are
     * currently available in the collection. Provides a clear indication
     * that collection bounds were violated.
     *
     * @return self New exception instance with default error message
     */
    public static function create(): self
    {
        return new self('Cannot take more shares than available');
    }
}
