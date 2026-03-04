<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Shamir;

use Cline\Shamir\Encoding\Base64Encoder;
use Cline\Shamir\Encoding\HexEncoder;
use Cline\Shamir\Exception\HexDecodingFailedException;
use Cline\Shamir\Exception\InsufficientSharesException;
use Cline\Shamir\Exception\InvalidChunkDataTypeException;
use Cline\Shamir\Exception\InvalidShareDataFormatException;
use Cline\Shamir\Exception\InvalidShareTypeException;
use Cline\Shamir\Exception\NoSharesProvidedException;
use Cline\Shamir\Exception\ShareChecksumMismatchException;
use Cline\Shamir\Exception\SharesDifferentThresholdsException;
use Cline\Shamir\Math\GaloisField;
use Cline\Shamir\Math\LagrangeInterpolation;

use const JSON_THROW_ON_ERROR;

use function count;
use function gmp_init;
use function gmp_strval;
use function hash;
use function hash_equals;
use function hex2bin;
use function implode;
use function is_array;
use function is_string;
use function json_decode;
use function mb_strlen;
use function throw_if;
use function throw_unless;

/**
 * Combines shares to reconstruct secrets using Shamir's Secret Sharing scheme.
 *
 * Implements the reconstruction phase of Shamir's algorithm using Lagrange
 * interpolation over finite fields. Validates share integrity through checksums
 * and threshold compatibility before reconstruction. Handles chunked secrets
 * automatically by reconstructing each chunk independently.
 *
 * @psalm-immutable
 * @author Brian Faust <brian@cline.sh>
 */
final readonly class SecretCombiner
{
    /**
     * The Galois Field used for polynomial arithmetic operations.
     */
    private GaloisField $field;

    /**
     * Create a new secret combiner instance.
     *
     * @param Config $config Configuration specifying prime field size and encoding format.
     *                       Must match the configuration used during secret splitting.
     */
    public function __construct(
        private Config $config = new Config(
        ),
    ) {
        $this->field = new GaloisField($this->config->prime);
    }

    /**
     * Reconstruct secret from shares using Lagrange interpolation.
     *
     * Validates all shares for compatibility and integrity before reconstructing
     * the original secret. Shares can be provided as Share objects or serialized
     * strings. Requires at least threshold number of shares to succeed.
     *
     * The reconstruction process:
     * 1. Normalizes all shares to Share objects
     * 2. Validates checksums and threshold compatibility
     * 3. Decodes share data and reconstructs each chunk independently
     * 4. Combines all chunks to recover the original secret
     *
     * @param iterable<Share|string> $shares Shares to combine (objects or strings)
     *
     * @throws HexDecodingFailedException         If hex to binary conversion fails
     * @throws InsufficientSharesException        If fewer shares than threshold
     * @throws InvalidChunkDataTypeException      If chunk data has wrong type
     * @throws InvalidShareDataFormatException    If decoded share data is malformed
     * @throws InvalidShareTypeException          If share has unexpected type
     * @throws NoSharesProvidedException          If shares array is empty
     * @throws ShareChecksumMismatchException     If any share has invalid checksum
     * @throws SharesDifferentThresholdsException If shares have mismatched thresholds
     *
     * @return string The reconstructed secret
     */
    public function combine(iterable $shares): string
    {
        $shareObjects = $this->normalizeShares($shares);

        // Validate shares
        $this->validateShares($shareObjects);

        // Get first share to determine threshold
        $firstShare = $shareObjects[0];
        $threshold = $firstShare->getThreshold();

        if (count($shareObjects) < $threshold) {
            throw InsufficientSharesException::notEnoughShares(count($shareObjects), $threshold);
        }

        // Get encoder
        $encoder = match ($this->config->encoding) {
            'hex' => new HexEncoder(),
            default => new Base64Encoder(),
        };

        // Decode share data
        /** @var array<int, array<int, string>> $shareDataArrays */
        $shareDataArrays = [];

        foreach ($shareObjects as $share) {
            $decoded = $encoder->decode($share->getValue());
            $data = json_decode($decoded, true, 512, JSON_THROW_ON_ERROR);

            throw_unless(is_array($data), InvalidShareDataFormatException::create());

            $shareDataArrays[] = $data;
        }

        // Reconstruct each chunk
        $chunkCount = count($shareDataArrays[0]);
        $reconstructedChunks = [];

        for ($chunkIndex = 0; $chunkIndex < $chunkCount; ++$chunkIndex) {
            $points = [];

            foreach ($shareObjects as $i => $share) {
                $y = $shareDataArrays[$i][$chunkIndex];

                throw_unless(is_string($y), InvalidChunkDataTypeException::create());

                $points[] = [
                    'x' => (string) $share->getIndex(),
                    'y' => $y,
                ];
            }

            $interpolation = new LagrangeInterpolation($this->field);
            $secretNumber = $interpolation->interpolate($points);

            // Handle empty secret (0 becomes empty string)
            if ($secretNumber === '0') {
                $reconstructedChunks[] = '';
            } else {
                // Convert decimal string to hex, then to bytes
                $hexString = gmp_strval(gmp_init($secretNumber), 16);

                // Ensure even length for hex2bin
                if (mb_strlen($hexString) % 2 !== 0) {
                    $hexString = '0'.$hexString;
                }

                $bytes = hex2bin($hexString);

                throw_if($bytes === false, HexDecodingFailedException::create());

                $reconstructedChunks[] = $bytes;
            }
        }

        // Combine all chunks
        return implode('', $reconstructedChunks);
    }

    /**
     * Normalize shares to Share objects.
     *
     * Accepts shares as either Share objects or serialized strings and converts
     * all to Share objects for uniform processing. This allows flexible input
     * formats while maintaining type safety internally.
     *
     * @param iterable<Share|string> $shares Mixed array of Share objects and/or serialized strings
     *
     * @throws InvalidShareTypeException If a share is neither string nor Share object
     *
     * @return array<Share> Normalized array of Share objects
     */
    private function normalizeShares(iterable $shares): array
    {
        $normalized = [];

        foreach ($shares as $share) {
            if (is_string($share)) {
                $normalized[] = Share::fromString($share);
            } elseif ($share instanceof Share) {
                $normalized[] = $share;
            } else {
                throw InvalidShareTypeException::create();
            }
        }

        return $normalized;
    }

    /**
     * Validate that shares are compatible and have valid checksums.
     *
     * Performs critical security checks to ensure shares can be safely combined:
     * - Verifies at least one share is provided
     * - Validates SHA-256 checksums to detect tampering or corruption
     * - Ensures all shares have matching thresholds (same split operation)
     *
     * @param array<Share> $shares Array of shares to validate
     *
     * @throws NoSharesProvidedException          If shares array is empty
     * @throws ShareChecksumMismatchException     If any checksum validation fails
     * @throws SharesDifferentThresholdsException If threshold values don't match
     */
    private function validateShares(array $shares): void
    {
        throw_if($shares === [], NoSharesProvidedException::create());

        $firstShare = $shares[0];
        $threshold = $firstShare->getThreshold();

        foreach ($shares as $share) {
            // Verify checksum
            $expectedChecksum = hash('sha256', $share->getValue());

            if (!hash_equals($expectedChecksum, $share->getChecksum())) {
                throw ShareChecksumMismatchException::create();
            }

            // Verify threshold matches
            if ($share->getThreshold() !== $threshold) {
                throw SharesDifferentThresholdsException::create();
            }
        }
    }
}
