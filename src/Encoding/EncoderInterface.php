<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Shamir\Encoding;

use Cline\Shamir\Exception\ShamirException;

/**
 * Contract for encoding and decoding share data to string representations.
 *
 * Defines the interface for converting binary share data to and from string
 * representations suitable for storage, transmission, or display. Implementations
 * determine the encoding scheme (base64, hex, etc.) used for serialization.
 *
 * Encoders must be bidirectional, ensuring that decode(encode($data)) === $data
 * for all valid binary input. Implementations should validate input during decoding
 * and throw appropriate exceptions for malformed data.
 *
 * @author Brian Faust <brian@cline.sh>
 */
interface EncoderInterface
{
    /**
     * Encode binary data to string representation.
     *
     * Converts binary share data into a string format suitable for storage or
     * transmission. The output format is determined by the implementation (base64,
     * hex, etc.) and should be consistent for encoding and decoding operations.
     *
     * @param string $data Binary data to encode, typically serialized share data
     *                     containing polynomial coefficients, metadata, and checksums
     *
     * @return string Encoded string representation of the binary data. The exact
     *                format and length depend on the implementation's encoding scheme.
     */
    public function encode(string $data): string;

    /**
     * Decode string representation back to binary data.
     *
     * Converts an encoded string back to its original binary form. Must reverse
     * the encoding operation performed by encode(). Implementations should validate
     * input and throw exceptions for malformed or invalid encoded strings.
     *
     * @param string $encoded Encoded string to decode. Must be in the format produced
     *                        by this encoder's encode() method.
     *
     * @throws ShamirException If decoding fails due to invalid
     *                         or malformed input data
     *
     * @return string Decoded binary data in its original form
     */
    public function decode(string $encoded): string;
}
