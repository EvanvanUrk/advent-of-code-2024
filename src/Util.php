<?php

namespace AoC;

use AoC\Util\Map2D;
use AoC\Util\Vec2D;
use AoC\Util\Route;

class Util
{
    public static function getInput(int $day, string $prefix = 'day'): string
    {
        return file_get_contents(__DIR__ . sprintf('/../input/%s%d.txt', $prefix, $day));
    }

    /**
     * @param string $input
     * @return string[]
     */
    public static function splitByLines(string $input): array
    {
        return explode(PHP_EOL, trim($input));
    }

    public static function getTime(): int
    {
        return round(microtime(true) * 1000);
    }

    /**
     * @param string|int|float $start
     * @param string|int|float $end
     * @return array<int, string|int|float>
     */
    public static function range(
        mixed $start,
        mixed $end,
        bool $reverse = false,
        int $step = 1,
    ): array {
        $range = range($start, $end, $step);
        return $reverse ? array_reverse($range) : $range;
    }

    public static function point(int $x, int $y): array
    {
        return ['x' => $x, 'y' => $y];
    }

    public static function cartesianProduct(array $a, array $b): array
    {
        $product = [];
        $added = [];
        foreach ($a as $itemA) {
            foreach ($b as $itemB) {
                if (false === in_array($itemA . '-' . $itemB, $added)
                    && false === in_array($itemB . '-' . $itemA, $added)) {
                    $product[] = [$itemA, $itemB];
                    $added[] = $itemA . '-' . $itemB;
                }
            }
        }

        return $product;
    }

    /**
     * Because in_array will always return true if you search for null...
     */
    public static function inArray(mixed $value, array $array): bool
    {
        foreach ($array as $val) {
            if ($val === $value) { return true; }
        }

        return false;
    }

    public static function rgbHexToIntArray(string $hex): array
    {
        return array_combine(['r', 'g', 'b'], array_map(
            fn(array $chunk) => (int) hexdec($chunk[0] . $chunk[1]),
            array_chunk(str_split(substr($hex, 1)), 2)
        ));
    }

    public static function asRgbOutput(
        string $val,
        int $r, int $g, int $b,
    ): string {
        return sprintf(
            "\033[48;2;0;0;0m\033[38;2;%d;%d;%dm%s\033[0m",
            $r, $g, $b, $val
        );
    }

    /**
     * Count cells within the area of a closed route. Assumes the route
     * contains only positions within the given map.
     */
    public static function countCellsInsideRoute(Route $route, Map2D $map): int
    {
        $direction = [];

        foreach ($route->get() as $idx => $cur) {
            if ($map->get($cur->x, $cur->y))
            $prev = array_slice(
                $route->get(),
                $idx - 1,
                1
            )[0];
            $next = array_slice(
                $route->get(),
                ($idx + 1) % $route->count(),
                1
            )[0];

            $key = $cur->getKey();
            if ($prev->y > $cur->y || $next->y < $cur->y) {
                $direction[$key] = true; // up
            } else if ($prev->y < $cur->y || $next->y > $cur->y) {
                $direction[$key] = false; // down
            }
        }

        $countStep = false;
        $count = 0;
        $clockwise = null;
        $newMap = $map->map(
            function($x, $y, $value)
                use ($direction, &$countStep, &$count, &$clockwise, $route)
            {
                $key = $x . '-' . $y;
                if (isset($direction[$key])) {
                    if (null === $clockwise) {
                        $clockwise = $direction[$key];
                    }
                    $countStep = $direction[$key] === $clockwise;
                }

                if ($route->hasKey($key)) { return $value; }

                if ($countStep) { $count += 1; }

                return $countStep ? 'I': 'O';
            }
        );

//        echo $newMap . PHP_EOL;

        return (string) $count;
    }

    public static function mod(int $dividend, int $divisor): int
    {
        $mod = $dividend % $divisor;
        return $mod < 0 ? $mod + $dividend : $mod;
    }
}
