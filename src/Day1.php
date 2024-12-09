<?php

namespace AoC;

use Illuminate\Support\Collection;

class Day1 implements Solution
{
    private Collection $collection;

    public function part1(string $input): string
    {
        $lists = [[], []];

        $this->collection = Collection::make(explode(PHP_EOL, trim($input)));
        $this->collection = $this->collection
            ->map(fn(string $line) => trim($line))
            ->map(fn(string $line) => explode('   ', $line))
            ->collect()
        ;

        $this->collection
            ->each(function(array $parts) use (&$lists) {
                $lists[0][] = $parts[0];
                $lists[1][] = $parts[1];
            });
        ;

        sort($lists[0]);
        sort($lists[1]);

        $distance = 0;
        foreach ($lists[0] as $i => $n) {
            $distance += abs($n - $lists[1][$i]);
        }

        return (string) $distance;
    }

    public function part2(string $input): string
    {
        $frequency = [];
        $this->collection->each(function(array $parts) use (&$frequency) {
            $frequency[$parts[1]] ??= 0;
            $frequency[$parts[1]] += 1;
        });

        $total = 0;
        $this->collection->each(function(array $parts) use ($frequency, &$total) {
            $total += $parts[0] * ($frequency[$parts[0]] ?? 0);
        });

        return (string) $total;
    }
}
