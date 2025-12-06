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
 * Base64 encoding for share data.
 * @psalm-immutable
 * @author Brian Faust <brian@cline.sh>
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

        throw_if($decoded === false, Base64DecodingFailedException::create());

        return $decoded;
    }
}
