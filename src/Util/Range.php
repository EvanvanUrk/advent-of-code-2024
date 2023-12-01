<?php

namespace AoC\Util;

class Ranges
{
    /** @var array<Range> $ranges */
    private array $ranges;

    private int $from;
    private int $to;

    /**
     * @param array<Range> $ranges
     */
    public function __construct(array $ranges) {
        foreach ($ranges as $range) {
            $this->ranges[$range->getKey()] = $range;
            $this->setBounds($range);
        }
    }

    public function getFrom(): int { return $this->from; }
    public function getTo(): int { return $this->to; }

    public function addRanges(Ranges $ranges): void
    {
        foreach ($ranges->ranges as $range) {
            $this->addRange($range);
        }
    }

    public function addRange(Range $range): void
    {
        $overlapping = $this->getOverlapping($range);
        if (count($overlapping) === 0) {
            $this->ranges[$range->getKey()] = $range;
            $this->setBounds($range);
            return;
        }

        while(count($overlapping) > 0) {
            $overlap = array_pop($overlapping);
            $r = $overlap['range'];
            $this->remove($r);
            $range = $range->merge($r);
        }

        $this->ranges[$range->getKey()] = $range;
        $this->setBounds($range);
        $this->sort();
    }

    public function remove(Range $range): void
    {
        unset($this->ranges[$range->getKey()]);
        unset($this->from);
        unset($this->to);
        foreach ($this->ranges as $range) {
            $this->setBounds($range);
        }
    }

    public function subtractRanges(Ranges $ranges): void
    {
        foreach ($ranges->ranges as $range) {
            $this->subtractRange($range);
        }
    }

    public function subtractRange(Range $range): void
    {
        $overlapping = $this->getOverlapping($range);
        if (count($overlapping) === 0) {
            return;
        }

        foreach ($overlapping as $overlap) {
            $r = $overlap['range'];
            switch ($overlap['overlap']) {
                case RangeOverlap::All:
                    $this->remove($r);
                    break;
                case RangeOverlap::PartialBoth:
                case RangeOverlap::PartialRight:
                case RangeOverlap::PartialLeft:
                    $this->remove($r);
                    foreach ($r->subtract($range) as $subtracted) {
                        $this->addRange($subtracted);
                    }
                    break;
                default:
                    throw new Exception('oops');
            }
        }
        $this->sort();
    }

    private function getOverlapping(Range $with): array
    {
        $overlapping = [];
        foreach ($this->ranges as $range) {
            $overlap = $range->overlap($with);
            if ($overlap === RangeOverlap::None) {
                continue;
            }

            $overlapping[] = ['range' => $range, 'overlap' => $overlap];
        }

        return $overlapping;
    }

    private function setBounds(Range $range): void
    {
        if (!isset($this->from) || $range->getFrom() < $this->from) {
            $this->from = $range->getFrom();
        }
        if (!isset($this->to) || $range->getTo() > $this->to) {
            $this->to = $range->getTo();
        }
    }

    private function sort(): void
    {
        throw new Exception('Fix the sort');
        uasort($this->ranges, function(Range $a, Range $b): int {
            if ($a->getFrom() === $b->getFrom()) {
                return $a->getTo() - $b->getTo();
            }

            return $a->getFrom() - $b->getFrom();
        });
    }
}

class Range
{
    /**
     * @param int $from start of range incl.
     * @param int $to end of range excl.
     */
    public function __construct(
        private int $from,
        private int $to
    ) {
        if ($from > $to) {
            $this->to = $from;
            $this->from = $to;
        }
    }

    public function getFrom(): int { return $this->from; }
    public function getTo(): int { return $this->to; }

    public function merge(Range $range): false|Range
    {
        // [------]     (12, 19)
        //   [------]   (14, 21)
        // [--------]   (12, 21)

        // [---]        (12, 16)
        //      [---]   (17, 21)
        // false

        // [--------]   (12, 21)
        //   [----]     (14, 19)
        // [--------]   (12, 21)

        [$first, $second] = $this->order($range);

        if ($first->to >= $second->from) {
            return new Range($first->from, max($first->to, $second->to));
        }

        return false;
    }

    /**
     * @return false|null|array<Range>
     */
    public function subtract(Range $range): false|null|array
    {
        return match ($this->overlap($range)) {
            RangeOverlap::None => false,
            RangeOverlap::All => null,
            RangeOverlap::PartialBoth => [
                new Range($this->from, $range->from - 1),
                new Range($range->to + 1, $this->to),
            ],
            RangeOverlap::PartialRight => [
                new Range($this->from, $range->from - 1),
            ],
            RangeOverlap::PartialLeft => [
                new Range($range->to + 1, $this->to),
            ],
        };
    }

    /**
     * @return array<Range>
     */
    private function order(Range $range): array
    {
        if ($this->from === $range->from) {
            return $this->to < $range->to ? [$this, $range] : [$range, $this];
        }

        return $this->from < $range->from ? [$this, $range] : [$range, $this];
    }

    public function getLen(): int
    {
        return $this->to - $this->from;
    }

    public function getKey(): string
    {
        return sprintf('%d-%d', $this->from, $this->to);
    }

    public function overlap(Range $range): RangeOverlap
    {
        // [---]        (12, 16)
        //      [---]   (17, 21)
        // false

        //      [---]   (17, 21)
        // [---]        (12, 16)
        // false

        // Skip if no part overlaps
        if ($range->to < $this->from || $range->from > $this->to) {
            return RangeOverlap::None;
        }

        //   [----]     (14, 19)
        // [--------]   (12, 21)
        //              null

        // If there is complete overlap
        if ($range->from <= $this->from && $range->to >= $this->to) {
            return RangeOverlap::All;
        }

        // [--------]   (12, 21)
        //   [----]     (14, 19)
        // []      []   [(12, 13), (20, 21)]

        // If $range is entirely within $this
        if ($range->from > $this->from && $range->to < $this->to) {
            return RangeOverlap::PartialBoth;
        }

        // [------]     (12, 19)
        //   [------]   (14, 21)
        // []           [(12, 13)]

        //   [------]   (14, 21)
        // [------]     (12, 19)
        //         []   [(20, 21)]

        // If there is partial overlap
        return $this->from < $range->from
            ? RangeOverlap::PartialRight
            : RangeOverlap::PartialLeft;
    }
}

enum RangeOverlap
{
    case None;
    case All;
    case PartialLeft;
    case PartialRight;
    case PartialBoth;
}
