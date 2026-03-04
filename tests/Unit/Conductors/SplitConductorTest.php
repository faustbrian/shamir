<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Unit\Conductors;

use Cline\Shamir\ShamirManager;
use Cline\Shamir\ShareCollection;
use InvalidArgumentException;

use function describe;
use function expect;
use function test;

describe('SplitConductor', function (): void {
    test('builds split configuration fluently', function (): void {
        $manager = new ShamirManager();
        $secret = 'test-secret';

        $shares = $manager->for($secret)
            ->threshold(2)
            ->shares(4)
            ->split();

        expect($shares)->toBeInstanceOf(ShareCollection::class)
            ->and($shares->count())->toBe(4);

        $reconstructed = $manager->combine($shares->take(2));
        expect($reconstructed)->toBe($secret);
    });

    test('is immutable', function (): void {
        $manager = new ShamirManager();
        $conductor1 = $manager->for('secret');
        $conductor2 = $conductor1->threshold(3);
        $conductor3 = $conductor2->shares(5);

        expect($conductor1)->not->toBe($conductor2)
            ->and($conductor2)->not->toBe($conductor3);
    });

    test('throws when threshold not set', function (): void {
        $manager = new ShamirManager();

        expect(fn (): ShareCollection => $manager->for('secret')->shares(5)->split())
            ->toThrow(InvalidArgumentException::class);
    });

    test('throws when shares not set', function (): void {
        $manager = new ShamirManager();

        expect(fn (): ShareCollection => $manager->for('secret')->threshold(3)->split())
            ->toThrow(InvalidArgumentException::class);
    });

    test('allows method chaining in any order', function (): void {
        $manager = new ShamirManager();

        $shares1 = $manager->for('secret')
            ->threshold(3)
            ->shares(5)
            ->split();

        $shares2 = $manager->for('secret')
            ->shares(5)
            ->threshold(3)
            ->split();

        expect($shares1->count())->toBe(5)
            ->and($shares2->count())->toBe(5);
    });
});
