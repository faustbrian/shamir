<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Shamir\Encoding;

/**
 * Interface for encoding/decoding share data.
 * @author Brian Faust <brian@cline.sh>
 */
interface EncoderInterface
{
    /**
     * Encode binary data to string representation.
     */
    public function encode(string $data): string;

    /**
     * Decode string representation back to binary data.
     */
    public function decode(string $encoded): string;
}
