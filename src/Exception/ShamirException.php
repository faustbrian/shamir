<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Shamir\Exception;

use Throwable;

/**
 * Marker interface for all exceptions thrown by the Shamir Secret Sharing package.
 *
 * This interface serves as a common type for all exceptions in the Shamir package,
 * enabling consumers to catch any package-specific exception with a single catch
 * block. All exception classes in this package implement this interface, providing
 * a unified exception hierarchy that simplifies error handling.
 *
 * Using this interface allows applications to distinguish between exceptions
 * originating from the Shamir package and other exceptions in the system. This
 * is particularly useful for logging, error reporting, and implementing package-
 * specific error handling strategies without catching all exceptions globally.
 *
 * ```php
 * try {
 *     $secret = Shamir::combine($shares);
 * } catch (ShamirException $e) {
 *     // Handle any Shamir-related exception
 *     logger()->error('Shamir operation failed', ['exception' => $e]);
 * }
 * ```
 *
 * The interface extends Throwable to ensure compatibility with PHP's exception
 * handling mechanism while providing package-specific type information. All
 * concrete exception classes extend either RuntimeException or InvalidArgumentException
 * and implement this interface.
 *
 * @author Brian Faust <brian@cline.sh>
 *
 * @see InvalidShareException For share validation errors
 * @see IncompatibleSharesException For share compatibility errors
 * @see InsufficientSharesException For threshold requirement errors
 */
interface ShamirException extends Throwable
{
    // Marker interface - no methods required
}
