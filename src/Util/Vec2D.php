<?php

declare(strict_types=1);

namespace AoC\Util;

class Vec2D
{
    public function __construct(
        public readonly int $x,
        public readonly int $y
    ) { }

    public function getKey(): string
    {
        return $this->x . '-' . $this->y;
    }

    public function add(Vec2D $point): Vec2D
    {
        return new Vec2D(
            $this->x + $point->x,
            $this->y + $point->y,
        );
    }

    public function sub(Vec2D $point): Vec2D
    {
        return new Vec2D(
            $this->x - $point->x,
            $this->y - $point->y
        );
    }

    public function opposite(): Vec2D
    {
        return new Vec2D(-$this->x, -$this->y);
    }
}
