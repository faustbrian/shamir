<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Unit;

use Cline\Shamir\Conductors\CombineConductor;
use Cline\Shamir\Conductors\SplitConductor;
use Cline\Shamir\Config;
use Cline\Shamir\ShamirManager;
use Cline\Shamir\ShareCollection;

use function describe;
use function expect;
use function test;

describe('ShamirManager', function (): void {
    test('splits and combines secret', function (): void {
        $manager = new ShamirManager();
        $secret = 'my-secret-key';

        $shares = $manager->split($secret, 3, 5);

        expect($shares)->toBeInstanceOf(ShareCollection::class)
            ->and($shares->count())->toBe(5);

        $reconstructed = $manager->combine($shares->take(3));

        expect($reconstructed)->toBe($secret);
    });

    test('provides fluent split API', function (): void {
        $manager = new ShamirManager();
        $secret = 'my-secret-key';

        $conductor = $manager->for($secret);

        expect($conductor)->toBeInstanceOf(SplitConductor::class);

        $shares = $conductor
            ->threshold(3)
            ->shares(5)
            ->split();

        expect($shares)->toBeInstanceOf(ShareCollection::class)
            ->and($shares->count())->toBe(5);
    });

    test('provides fluent combine API', function (): void {
        $manager = new ShamirManager();
        $shares = $manager->split('secret', 2, 3);

        $conductor = $manager->from($shares);

        expect($conductor)->toBeInstanceOf(CombineConductor::class);

        $reconstructed = $conductor->combine();

        expect($reconstructed)->toBe('secret');
    });

    test('verifies share compatibility', function (): void {
        $manager = new ShamirManager();
        $shares = $manager->split('secret', 3, 5)->toArray();

        $compatible = $manager->areCompatible($shares[0], $shares[1], $shares[2]);

        expect($compatible)->toBeTrue();
    });

    test('detects incompatible shares', function (): void {
        $manager = new ShamirManager();
        $shares1 = $manager->split('secret1', 2, 3)->toArray();
        $shares2 = $manager->split('secret2', 3, 5)->toArray();

        $compatible = $manager->areCompatible($shares1[0], $shares2[0]);

        expect($compatible)->toBeFalse();
    });

    test('creates new manager with different config', function (): void {
        $manager = new ShamirManager();
        $newConfig = new Config(prime: Config::PRIME_512, encoding: 'hex');

        $newManager = $manager->withConfig($newConfig);

        expect($newManager)->toBeInstanceOf(ShamirManager::class)
            ->and($newManager)->not->toBe($manager)
            ->and($newManager->getConfig())->toBe($newConfig);
    });

    test('returns current config', function (): void {
        $config = new Config(prime: Config::PRIME_256, encoding: 'base64');
        $manager = new ShamirManager($config);

        expect($manager->getConfig())->toBe($config);
    });
});
