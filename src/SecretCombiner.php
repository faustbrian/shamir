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
use Cline\Shamir\Exception\IncompatibleSharesException;
use Cline\Shamir\Exception\InsufficientSharesException;
use Cline\Shamir\Exception\InvalidShareException;
use Cline\Shamir\Math\GaloisField;
use Cline\Shamir\Math\LagrangeInterpolation;
use InvalidArgumentException;
use RuntimeException;

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
 * @psalm-immutable
 */
final readonly class SecretCombiner
{
    private GaloisField $field;

    public function __construct(
        private Config $config = new Config(
        ),
    ) {
        $this->field = new GaloisField($this->config->prime);
    }

    /**
     * Reconstruct secret from shares.
     *
     * @param iterable<Share|string> $shares
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

            throw_unless(is_array($data), RuntimeException::class, 'Invalid share data format');

            $shareDataArrays[] = $data;
        }

        // Reconstruct each chunk
        $chunkCount = count($shareDataArrays[0]);
        $reconstructedChunks = [];

        for ($chunkIndex = 0; $chunkIndex < $chunkCount; ++$chunkIndex) {
            $points = [];

            foreach ($shareObjects as $i => $share) {
                $y = $shareDataArrays[$i][$chunkIndex];

                throw_unless(is_string($y), RuntimeException::class, 'Invalid chunk data type');

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

                throw_if($bytes === false, RuntimeException::class, 'Failed to decode hex string');

                $reconstructedChunks[] = $bytes;
            }
        }

        // Combine all chunks
        return implode('', $reconstructedChunks);
    }

    /**
     * Normalize shares to Share objects.
     *
     * @param iterable<Share|string> $shares
     *
     * @return array<Share>
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
                throw new InvalidArgumentException('Shares must be Share objects or strings');
            }
        }

        return $normalized;
    }

    /**
     * Validate that shares are compatible and have valid checksums.
     *
     * @param array<Share> $shares
     */
    private function validateShares(array $shares): void
    {
        throw_if($shares === [], InvalidArgumentException::class, 'No shares provided');

        $firstShare = $shares[0];
        $threshold = $firstShare->getThreshold();

        foreach ($shares as $share) {
            // Verify checksum
            $expectedChecksum = hash('sha256', $share->getValue());

            if (!hash_equals($expectedChecksum, $share->getChecksum())) {
                throw InvalidShareException::checksumMismatch();
            }

            // Verify threshold matches
            if ($share->getThreshold() !== $threshold) {
                throw IncompatibleSharesException::differentThresholds();
            }
        }
    }
}
