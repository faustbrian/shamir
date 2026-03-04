<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Unit\Conductors;

use Cline\Shamir\ShamirManager;
use Cline\Shamir\Share;

use function array_map;
use function describe;
use function expect;
use function test;

describe('CombineConductor', function (): void {
    test('combines shares fluently', function (): void {
        $manager = new ShamirManager();
        $secret = 'test-secret';

        $shares = $manager->split($secret, 2, 3);

        $reconstructed = $manager->from($shares)->combine();

        expect($reconstructed)->toBe($secret);
    });

    test('accepts share strings', function (): void {
        $manager = new ShamirManager();
        $secret = 'test-secret';

        $shares = $manager->split($secret, 2, 3);
        $shareStrings = array_map(fn (Share $share): string => $share->toString(), $shares->toArray());

        $reconstructed = $manager->from($shareStrings)->combine();

        expect($reconstructed)->toBe($secret);
    });

    test('works with iterable shares', function (): void {
        $manager = new ShamirManager();
        $secret = 'test-secret';

        $shares = $manager->split($secret, 2, 3);

        $reconstructed = $manager->from($shares->toArray())->combine();

        expect($reconstructed)->toBe($secret);
    });
});
