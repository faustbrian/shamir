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
 *
 * Implements the sharing phase of Shamir's algorithm by creating random
 * polynomials where the secret is the constant term. Evaluating the polynomial
 * at different points generates shares. Automatically chunks large secrets
 * to fit within the finite field size, processing each chunk independently.
 *
 * @psalm-immutable
 * @author Brian Faust <brian@cline.sh>
 */
final readonly class SecretSplitter
{
    /**
     * The Galois Field used for polynomial arithmetic operations.
     */
    private GaloisField $field;

    /**
     * Create a new secret splitter instance.
     *
     * @param Config $config Configuration specifying prime field size and encoding format.
     *                       The prime must be large enough to contain the secret chunks.
     */
    public function __construct(
        private Config $config = new Config(
        ),
    ) {
        $this->field = new GaloisField($this->config->prime);
    }

    /**
     * Split a secret into n shares with threshold k.
     *
     * Creates shares using polynomial interpolation where any k shares can
     * reconstruct the original secret. The secret is divided into chunks that
     * fit within the field, each chunk is split independently, and the results
     * are combined into final shares with metadata and checksums.
     *
     * Security properties:
     * - Any k or more shares can reconstruct the secret
     * - Fewer than k shares reveal no information about the secret
     * - Each share is cryptographically independent
     *
     * @param string $secret    The secret to split (any binary string)
     * @param int    $threshold Minimum shares required to reconstruct (k >= 2)
     * @param int    $shares    Total number of shares to generate (n >= k)
     *
     * @throws Exception                       If random polynomial generation fails
     * @throws ThresholdExceedsSharesException If threshold > shares
     * @throws ThresholdTooLowException        If threshold < 2
     *
     * @return ShareCollection Collection of generated shares with metadata
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
     * Divides the secret into fixed-size chunks that can safely fit within
     * the Galois Field's prime modulus. For a 256-bit prime, uses 30-byte
     * chunks to ensure the numeric representation stays within field bounds.
     * Empty secrets return a single empty chunk for proper handling.
     *
     * @param string $secret The secret to chunk (binary string)
     *
     * @return array<int, string> Array of secret chunks (binary strings)
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
     * Split a single chunk into shares using polynomial evaluation.
     *
     * Converts the chunk to a numeric representation, creates a random polynomial
     * with the chunk value as the constant term, then evaluates the polynomial
     * at points 1 through n to generate shares. The polynomial degree is threshold-1,
     * ensuring that k points are needed to reconstruct the polynomial (and thus the secret).
     *
     * @param string $chunk     The chunk to split (binary string)
     * @param int    $threshold Minimum shares needed for reconstruction
     * @param int    $shares    Total number of shares to generate
     *
     * @throws Exception If random polynomial generation fails
     *
     * @return array<int, string> Share values indexed by share number (1-based)
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
     * Takes share values from all chunks and combines them into final Share objects.
     * Each final share contains the share values for all chunks, along with metadata
     * (index, threshold) and a SHA-256 checksum for integrity verification.
     * The share data is encoded using the configured encoding format (hex or base64).
     *
     * @param array<int, array<int, string>> $allShares Array of chunk shares where each element
     *                                                  is an array of share values indexed by share number
     * @param int                            $threshold The threshold value to embed in each share
     *
     * @return ShareCollection Collection of complete shares ready for distribution
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
     *
     * Creates a SHA-256 hash of the share data for integrity verification
     * during reconstruction. This allows detection of corrupted or tampered
     * shares before attempting to reconstruct the secret.
     *
     * @param string $data The encoded share data to hash
     *
     * @return string SHA-256 hash in hexadecimal format
     */
    private function generateChecksum(string $data): string
    {
        return hash('sha256', $data);
    }
}
