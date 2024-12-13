<?php

namespace AoC;

use Illuminate\Support\Collection;

class Day2 implements Solution
{
    private Collection $collection;

    public function part1(string $input): string
    {
        $this->collection = Collection::make(Util::splitByLines($input));
        $this->collection = $this->collection
            ->map(fn(string $line) => explode(' ', $line))
            ->collect()
        ;

        $safeReports = $this->collection->filter(fn(array $report) => $this->isReportSafe($report));

        return (string) $safeReports->count();
    }

    public function part2(string $input): string
    {
        $safeReports = $this->collection->filter(function(array $report): bool {
            if ($this->isReportSafe($report)) {
                return true;
            }

            for ($i = 0; $i < count($report); $i += 1) {
                $splicedReport = $report;
                array_splice($splicedReport, $i, 1);
                if ($this->isReportSafe($splicedReport)) {
                    return true;
                }
            }

            return false;
        });

        return (string) $safeReports->count();
    }

    private function isReportSafe(array $report): bool
    {
        if (count($report) <= 1) {
            return true;
        }

        $prev = array_shift($report);
        $asc = null;
        while ($cur = array_shift($report)) {
            if (abs($cur - $prev) > 3 || abs($cur - $prev) === 0) {
                return false;
            }

            if ($asc === null) {
                $asc = $cur > $prev;
            } elseif ($asc !== $cur > $prev) {
                return false;
            }

            $prev = $cur;
        }

        return true;
    }
}
