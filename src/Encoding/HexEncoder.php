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
 * Hexadecimal encoding for share data.
 * @psalm-immutable
 * @author Brian Faust <brian@cline.sh>
 */
final readonly class HexEncoder implements EncoderInterface
{
    #[Override()]
    public function encode(string $data): string
    {
        return bin2hex($data);
    }

    #[Override()]
    public function decode(string $encoded): string
    {
        $decoded = hex2bin($encoded);

        throw_if($decoded === false, HexDecodingFailedException::create());

        return $decoded;
    }
}
