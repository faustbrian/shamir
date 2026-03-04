<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Shamir\Encoding;

use Cline\Shamir\Exception\HexDecodingFailedException;
use Override;

use function bin2hex;
use function hex2bin;
use function throw_if;

/**
 * Hexadecimal encoder for share data serialization.
 *
 * Encodes binary share data into hexadecimal strings for storage and transmission.
 * Hex encoding produces human-readable strings using only 0-9 and a-f characters,
 * making it safe for URLs and easy to inspect but resulting in longer output
 * (200% of original length) compared to base64 encoding.
 *
 * This encoder validates decoded input to ensure only valid hex characters are
 * processed, maintaining data integrity throughout the encoding cycle.
 *
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
final readonly class HexEncoder implements EncoderInterface
{
    /**
     * Encode binary data to hexadecimal string representation.
     *
     * Converts binary share data into a lowercase hexadecimal string. Each byte
     * is represented by two hex characters (0-9, a-f), making the output length
     * exactly twice the input length.
     *
     * @param string $data Binary data to encode, typically serialized share data
     *                     containing polynomial coefficients and metadata
     *
     * @return string Hexadecimal representation of the input data. Output contains
     *                only lowercase hex characters (0-9, a-f) and is exactly twice
     *                the length of the input.
     */
    #[Override()]
    public function encode(string $data): string
    {
        return bin2hex($data);
    }

    /**
     * Decode hexadecimal string back to binary data.
     *
     * Converts a hexadecimal string back to its original binary form. Validates
     * that the input contains only valid hex characters (0-9, a-f, A-F) and has
     * an even length. Invalid input results in an exception.
     *
     * @param string $encoded Hexadecimal string to decode. Must contain only valid
     *                        hex characters (0-9, a-f, A-F) and have even length.
     *                        Case-insensitive.
     *
     * @throws HexDecodingFailedException If the input contains invalid characters,
     *                                    has odd length, or is otherwise malformed
     *
     * @return string Decoded binary data in its original form
     */
    #[Override()]
    public function decode(string $encoded): string
    {
        $decoded = hex2bin($encoded);

        throw_if($decoded === false, HexDecodingFailedException::create());

        return $decoded;
    }
}
