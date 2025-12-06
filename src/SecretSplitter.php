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
use Cline\Shamir\Exception\ThresholdExceedsSharesException;
use Cline\Shamir\Exception\ThresholdTooLowException;
use Cline\Shamir\Math\GaloisField;
use Cline\Shamir\Math\Polynomial;
use Exception;

use const JSON_THROW_ON_ERROR;

use function bin2hex;
use function count;
use function gmp_init;
use function gmp_strval;
use function hash;
use function json_encode;
use function mb_strlen;
use function mb_substr;
use function throw_if;

/**
 * Splits secrets into shares using Shamir's Secret Sharing scheme.
 * @psalm-immutable
 * @author Brian Faust <brian@cline.sh>
 */
final readonly class SecretSplitter
{
    private GaloisField $field;

    public function __construct(
        private Config $config = new Config(
        ),
    ) {
        $this->field = new GaloisField($this->config->prime);
    }

    /**
     * Split a secret into n shares with threshold k.
     *
     * @throws Exception
     */
    public function split(string $secret, int $threshold, int $shares): ShareCollection
    {
        throw_if($threshold < 2, ThresholdTooLowException::create());

        throw_if($threshold > $shares, ThresholdExceedsSharesException::create());

        // Convert secret to chunks that fit in the field
        $chunks = $this->chunkSecret($secret);
        $allShares = [];

        // Process each chunk independently
        foreach ($chunks as $chunk) {
            $chunkShares = $this->splitChunk($chunk, $threshold, $shares);
            $allShares[] = $chunkShares;
        }

        // Combine chunk shares into final shares
        return $this->combineChunkShares($allShares, $threshold);
    }

    /**
     * Break secret into chunks that fit in the field.
     *
     * @return array<int, string>
     */
    private function chunkSecret(string $secret): array
    {
        if ($secret === '') {
            return [''];
        }

        // For 256-bit prime, we can safely handle ~30 bytes per chunk
        $chunkSize = 30;
        $chunks = [];

        for ($i = 0; $i < mb_strlen($secret, '8bit'); $i += $chunkSize) {
            $chunks[] = mb_substr($secret, $i, $chunkSize, '8bit');
        }

        return $chunks;
    }

    /**
     * Split a single chunk into shares.
     *
     * @throws Exception
     * @return array<int, string> Shares indexed by share number (1-based)
     */
    private function splitChunk(string $chunk, int $threshold, int $shares): array
    {
        // Convert chunk to number (secret) - convert hex to decimal string
        $hexString = bin2hex($chunk);
        $secretNumber = $hexString !== '' ? gmp_strval(gmp_init($hexString, 16)) : '0';

        // Create random polynomial with secret as constant term
        $polynomial = Polynomial::random(
            $this->field,
            $threshold - 1,
            $secretNumber,
        );

        // Evaluate polynomial at points 1, 2, ..., n to generate shares
        $chunkShares = [];

        for ($i = 1; $i <= $shares; ++$i) {
            $x = (string) $i;
            $y = $polynomial->evaluate($x);
            $chunkShares[$i] = $y;
        }

        return $chunkShares;
    }

    /**
     * Combine chunk shares into final shares with metadata.
     *
     * @param array<int, array<int, string>> $allShares Array of chunk shares
     */
    private function combineChunkShares(array $allShares, int $threshold): ShareCollection
    {
        $shareCount = count($allShares[0]);
        $shares = [];

        // Get encoder
        $encoder = match ($this->config->encoding) {
            'hex' => new HexEncoder(),
            default => new Base64Encoder(),
        };

        for ($shareIndex = 1; $shareIndex <= $shareCount; ++$shareIndex) {
            $shareData = [];

            foreach ($allShares as $chunkShares) {
                $shareData[] = $chunkShares[$shareIndex];
            }

            // Encode share data
            $encoded = $encoder->encode(json_encode($shareData, JSON_THROW_ON_ERROR));

            // Generate checksum
            $checksum = $this->generateChecksum($encoded);

            $shares[] = new Share($shareIndex, $encoded, $threshold, $checksum);
        }

        return new ShareCollection($shares);
    }

    /**
     * Generate checksum for share validation.
     */
    private function generateChecksum(string $data): string
    {
        return hash('sha256', $data);
    }
}
