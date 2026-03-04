<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

pest()->in(__DIR__);

/**
 * Generate all combinations of k items from array.
 *
 * @param array<mixed> $items
 *
 * @return array<array<mixed>>
 */
function combinations(array $items, int $k): array
{
    $result = [];
    $n = count($items);

    if ($k > $n || $k <= 0) {
        return [];
    }

    if ($k === $n) {
        return [$items];
    }

    if ($k === 1) {
        return array_map(static fn ($item): array => [$item], $items);
    }

    for ($i = 0; $i <= $n - $k; ++$i) {
        $head = array_slice($items, $i, 1);
        $tailCombinations = combinations(array_slice($items, $i + 1), $k - 1);

        foreach ($tailCombinations as $tail) {
            $result[] = array_merge($head, $tail);
        }
    }

    return $result;
}
