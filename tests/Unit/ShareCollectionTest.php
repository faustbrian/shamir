<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Shamir\Exception\InvalidShareException;
use Cline\Shamir\Share;
use Cline\Shamir\ShareCollection;

describe('ShareCollection', function (): void {
    test('counts shares', function (): void {
        $shares = [
            new Share(1, 'value1', 3, 'check1'),
            new Share(2, 'value2', 3, 'check2'),
            new Share(3, 'value3', 3, 'check3'),
        ];

        $collection = new ShareCollection($shares);

        expect($collection->count())->toBe(3);
    });

    test('gets share by index', function (): void {
        $shares = [
            new Share(1, 'value1', 3, 'check1'),
            new Share(2, 'value2', 3, 'check2'),
            new Share(3, 'value3', 3, 'check3'),
        ];

        $collection = new ShareCollection($shares);

        expect($collection->get(2)->getValue())->toBe('value2');
    });

    test('throws when share index not found', function (): void {
        $collection = new ShareCollection([
            new Share(1, 'value1', 3, 'check1'),
        ]);

        $collection->get(99);
    })->throws(InvalidShareException::class);

    test('takes first N shares', function (): void {
        $shares = [
            new Share(1, 'value1', 3, 'check1'),
            new Share(2, 'value2', 3, 'check2'),
            new Share(3, 'value3', 3, 'check3'),
        ];

        $collection = new ShareCollection($shares);
        $subset = $collection->take(2);

        expect($subset->count())->toBe(2)
            ->and($subset->toArray()[0]->getIndex())->toBe(1)
            ->and($subset->toArray()[1]->getIndex())->toBe(2);
    });

    test('gets random shares', function (): void {
        $shares = [
            new Share(1, 'value1', 3, 'check1'),
            new Share(2, 'value2', 3, 'check2'),
            new Share(3, 'value3', 3, 'check3'),
            new Share(4, 'value4', 3, 'check4'),
            new Share(5, 'value5', 3, 'check5'),
        ];

        $collection = new ShareCollection($shares);
        $random = $collection->random(3);

        expect($random->count())->toBe(3);
    });

    test('is iterable', function (): void {
        $shares = [
            new Share(1, 'value1', 3, 'check1'),
            new Share(2, 'value2', 3, 'check2'),
        ];

        $collection = new ShareCollection($shares);
        $indices = [];

        foreach ($collection as $share) {
            $indices[] = $share->getIndex();
        }

        expect($indices)->toBe([1, 2]);
    });

    test('converts to array', function (): void {
        $shares = [
            new Share(1, 'value1', 3, 'check1'),
            new Share(2, 'value2', 3, 'check2'),
        ];

        $collection = new ShareCollection($shares);

        expect($collection->toArray())->toBe($shares);
    });

    test('serializes to JSON', function (): void {
        $shares = [
            new Share(1, 'value1', 3, 'check1'),
            new Share(2, 'value2', 3, 'check2'),
        ];

        $collection = new ShareCollection($shares);
        $json = json_encode($collection, \JSON_THROW_ON_ERROR);

        expect($json)->toBeJson();
    });

    test('prepares shares for distribution', function (): void {
        $shares = [
            new Share(1, 'value1', 3, 'check1'),
            new Share(2, 'value2', 3, 'check2'),
            new Share(3, 'value3', 3, 'check3'),
        ];

        $collection = new ShareCollection($shares);
        $distribution = $collection->forDistribution();

        // Should be indexed by share index
        expect($distribution)->toHaveKey(1)
            ->toHaveKey(2)
            ->toHaveKey(3);

        // Should contain all shares
        expect(count($distribution))->toBe(3);
    });
});
