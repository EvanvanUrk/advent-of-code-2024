<?php

declare(strict_types=1);

namespace AoC\Util;

use Stringable;

class Map3D
{
    /** @var array<int, array<int, array<int, null|string|Stringable>>> */
    private array $map;
    private array $set = [];

    public function __construct(
        private int $w,
        private int $h,
        private int $d,
    ) {
        $this->map = array_fill(
            0, $w,
            array_fill(
                0, $h,
                array_fill(
                    0, $d,
                    null
                )
            )
        );
    }

    public function get(Vec3D $point): null|string|Stringable
    {
        if (!$this->inBounds($point)) {
            return null;
        }

        [$x, $y, $z] = $point->asArray();

        return $this->map[$x][$y][$z];
    }

    public function set(Vec3D $point, null|string|Stringable $value): void
    {
        if ($this->inBounds($point)) {
            return;
        }

        $this->map[$point->x][$point->y][$point->z] = $value;

        if ($value === null) {
            unset($this->set[$point->getKey()]);
        } else {
            $this->set[$point->getKey()] = true;
        }
    }

    public function has(Vec3D $point): bool
    {
        return isset($this->set[$point->getKey()]);
    }

    public function inBounds(Vec3D $point): bool
    {
        [$x, $y, $z] = $point->asArray();

        return $x >= 0 && $y >= 0 && $z >= 0
            && $x < $this->w && $y < $this->h && $z < $this->d;
    }

    public function __toString(): string
    {
        $str = '';

        $append = function(array $vals) use (&$str) {
            $vals = array_filter(
                $vals,
                fn(null|string|Stringable $val) => $val !== null
            );

            $count = count($vals);
            if ($count < 1) {
                $str .= ' .';
            } elseif($count > 1) {
                $str .= ' ?';
            } else {
                $str .= $vals[0];
            }
        };

        // x by z - front
        foreach (range(0, $this->w - 1) as $x) {
            $str .= ' ' . $x;
        }
        $str .= PHP_EOL;

        foreach (range($this->d - 1, 0) as $z) {
            foreach (range(0, $this->w - 1) as $x) {
                $vals = [];
                foreach (range(0, $this->h - 1) as $y) {
                    $vals[] = $this->get(new Vec3D($x, $y, $z));
                }

                $append($vals);
            }
            $str .= ' ' . $z . PHP_EOL;
        }

        $str .= PHP_EOL;

        // y by z - side
        foreach (range(0, $this->h - 1) as $y) {
            $str .= ' ' . $y;
        }
        $str .= PHP_EOL;

        foreach (range($this->d - 1, 0) as $z) {
            foreach (range(0, $this->h - 1) as $y) {
                $vals = [];
                foreach (range(0, $this->w - 1) as $x) {
                    $vals[] = $this->get(new Vec3D($x, $y, $z));
                }

                $append($vals);
            }
            $str .= ' ' . $z . PHP_EOL;
        }

        $str .= PHP_EOL;

        // x by y - top
        // ...

        return $str;
    }
}
