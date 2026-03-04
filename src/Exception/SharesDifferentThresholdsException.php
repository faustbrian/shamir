<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Shamir\Exception;

/**
 * Exception thrown when shares have different threshold values during reconstruction.
 *
 * The threshold represents the minimum number of shares required to reconstruct
 * a secret and is established during the split operation. All shares from the
 * same split must carry identical threshold values. This exception is raised
 * when attempting to combine shares with mismatched thresholds, proving they
 * originated from different split operations with incompatible configurations.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class SharesDifferentThresholdsException extends IncompatibleSharesException
{
    /**
     * Creates a new exception instance indicating threshold incompatibility.
     *
     * The error message explicitly states that shares have different threshold
     * values and therefore must originate from separate split operations, making
     * them incompatible for secret reconstruction.
     *
     * @return self A new exception instance with a descriptive incompatibility message
     */
    public static function create(): self
    {
        return new self('Shares have different thresholds - they are from different split operations');
    }
}
