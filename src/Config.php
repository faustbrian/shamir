<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Shamir;

/**
 * Configuration for Shamir's Secret Sharing.
 * @psalm-immutable
 */
final readonly class Config
{
    /**
     * 128-bit prime (for secrets up to ~16 bytes per chunk).
     */
    public const string PRIME_128 = '340282366920938463463374607431768211297';

    /**
     * 256-bit prime (for secrets up to ~32 bytes per chunk).
     */
    public const string PRIME_256 = '115792089237316195423570985008687907853269984665640564039457584007913129639747';

    /**
     * 512-bit prime (for larger secrets).
     */
    public const string PRIME_512 = '13407807929942597099574024998205846127479365820592393377723561443721764030073546976801874298166903427690031858186486050853753882811946569946433649006084171';

    public function __construct(
        public string $prime = self::PRIME_256,
        public string $encoding = 'base64',
    ) {}
}
