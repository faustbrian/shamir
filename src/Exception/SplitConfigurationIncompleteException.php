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
 * Exception thrown when attempting to split a secret with incomplete configuration.
 *
 * Before performing a secret split operation, both the threshold (minimum shares
 * required for reconstruction) and the total number of shares to generate must
 * be explicitly configured. This exception is raised when the split operation
 * is invoked without setting one or both of these required configuration values.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class SplitConfigurationIncompleteException extends InvalidArgumentException implements ShamirException
{
    /**
     * Creates a new exception instance indicating incomplete split configuration.
     *
     * The error message explicitly requires both threshold and shares to be
     * configured before attempting the split operation, guiding users toward
     * proper API usage.
     *
     * @return self A new exception instance with configuration requirements message
     */
    public static function create(): self
    {
        return new self('Both threshold and shares must be set before splitting');
    }
}
