<?php

include_once 'vendor/autoload.php';

use AoC\Solution;

$start = \AoC\Util::getTime();

$day = (int) $argv[1];

$input = \AoC\Util::getInput($day, $argv[2] ?? 'day');

$class = '\AoC\Day' . $day;
$solution = new $class($input);

if (false === (is_object($solution)) || !($solution instanceof Solution)) {
    throw new Exception('Solution for day %d does not exist or does not implement AoC\Solution');
}

$part1 = $solution->part1($input);
$part1Runtime = \AoC\Util::getTime() - $start;

echo sprintf(
    "Part 1:\n%s\nCompleted in %dms\n\n",
    $part1,
    $part1Runtime
);

$start = \AoC\Util::getTime();
$part2 = $solution->part2($input);
$part2Runtime = \AoC\Util::getTime() - $start;

echo sprintf(
    "Part 2:\n%s\nCompleted in %dms\n",
    $part2,
    $part2Runtime
);
