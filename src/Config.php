<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Shamir;

/**
 * Configuration for Shamir's Secret Sharing operations.
 *
 * Provides configuration options for the finite field prime modulus and encoding
 * scheme used in secret sharing operations. The prime determines the maximum size
 * of secret chunks that can be processed, while the encoding controls how shares
 * are represented as strings.
 *
 * The finite field arithmetic operates modulo a large prime number. Larger primes
 * allow for larger secret chunks but increase computational overhead. Choose the
 * smallest prime that accommodates your secret size for optimal performance.
 *
 * ```php
 * // Use default 256-bit prime with base64 encoding
 * $config = new Config();
 *
 * // Use larger prime for bigger secrets
 * $config = new Config(prime: Config::PRIME_512);
 *
 * // Use hex encoding instead of base64
 * $config = new Config(encoding: 'hex');
 * ```
 *
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
final readonly class Config
{
    /**
     * 128-bit prime modulus for finite field arithmetic.
     *
     * Suitable for secrets up to approximately 16 bytes per chunk. Provides good
     * performance for small secrets while maintaining cryptographic security.
     * Value: 2^128 - 159 (a known safe prime).
     */
    public const string PRIME_128 = '340282366920938463463374607431768211297';

    /**
     * 256-bit prime modulus for finite field arithmetic.
     *
     * Suitable for secrets up to approximately 32 bytes per chunk. This is the
     * default prime, offering a good balance between security and performance
     * for most use cases. Value: 2^256 - 2^32 - 977 (secp256k1 field prime).
     */
    public const string PRIME_256 = '115792089237316195423570985008687907853269984665640564039457584007913129639747';

    /**
     * 512-bit prime modulus for finite field arithmetic.
     *
     * Suitable for larger secrets requiring more than 32 bytes per chunk. Provides
     * the highest security at the cost of increased computational overhead. Use only
     * when secret size requires it.
     */
    public const string PRIME_512 = '13407807929942597099574024998205846127479365820592393377723561443721764030073546976801874298166903427690031858186486050853753882811946569946433649006084171';

    /**
     * Create a new configuration instance.
     *
     * @param string $prime    The prime modulus to use for finite field arithmetic. Determines
     *                         the maximum chunk size and security level. Must be a prime number
     *                         represented as a decimal string. Use one of the PRIME_* constants
     *                         for tested, secure values. Default: PRIME_256.
     * @param string $encoding The encoding scheme for share string representation. Supported
     *                         values are 'base64' (compact, URL-unfriendly) and 'hex' (verbose,
     *                         human-readable). Default: 'base64'.
     */
    public function __construct(
        public string $prime = self::PRIME_256,
        public string $encoding = 'base64',
    ) {}
}
