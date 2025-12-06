<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Shamir\Encoding;

use Cline\Shamir\Exception\Base64DecodingFailedException;
use Override;

use function base64_decode;
use function base64_encode;
use function throw_if;

/**
 * Base64 encoder for share data serialization.
 *
 * Encodes binary share data into base64 strings for storage and transmission.
 * Base64 encoding is compact and widely supported but produces strings that
 * may contain URL-unsafe characters (+, /, =).
 *
 * This encoder uses strict mode for decoding to ensure data integrity, rejecting
 * any input that contains invalid base64 characters.
 *
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
final readonly class Base64Encoder implements EncoderInterface
{
    /**
     * Encode binary data to base64 string representation.
     *
     * Converts binary share data into a base64-encoded string suitable for
     * storage in text-based formats (JSON, databases, files) or transmission
     * over text-based protocols.
     *
     * @param string $data Binary data to encode, typically serialized share data
     *                     containing polynomial coefficients and metadata
     *
     * @return string Base64-encoded representation of the input data. Output length
     *                is approximately 133% of input length due to base64 overhead.
     */
    #[Override()]
    public function encode(string $data): string
    {
        return base64_encode($data);
    }

    /**
     * Decode base64 string back to binary data.
     *
     * Converts a base64-encoded string back to its original binary form. Uses
     * strict mode to validate input, ensuring that only valid base64 characters
     * are accepted. Any deviation from valid base64 format results in an exception.
     *
     * @param string $encoded Base64-encoded string to decode. Must contain only
     *                        valid base64 characters (A-Z, a-z, 0-9, +, /, =).
     *
     * @throws Base64DecodingFailedException If the input contains invalid base64
     *                                       characters or is malformed
     *
     * @return string Decoded binary data in its original form
     */
    #[Override()]
    public function decode(string $encoded): string
    {
        $decoded = base64_decode($encoded, true);

        throw_if($decoded === false, Base64DecodingFailedException::create());

        return $decoded;
    }
}
