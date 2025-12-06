<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Shamir\Encoding;

use Override;
use RuntimeException;

use function base64_decode;
use function base64_encode;
use function throw_if;

/**
 * Base64 encoding for share data.
 * @psalm-immutable
 */
final readonly class Base64Encoder implements EncoderInterface
{
    #[Override()]
    public function encode(string $data): string
    {
        return base64_encode($data);
    }

    #[Override()]
    public function decode(string $encoded): string
    {
        $decoded = base64_decode($encoded, true);

        throw_if($decoded === false, RuntimeException::class, 'Failed to decode base64 string');

        return $decoded;
    }
}
