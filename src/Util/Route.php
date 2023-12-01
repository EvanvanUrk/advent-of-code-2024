<?php

declare(strict_types=1);

namespace AoC\Util;

use mysql_xdevapi\Warning;

class Route
{
    private ?Vec2D $head = null;

    private int $len = 0;

    /** @var array<string, int> */
    private array $set = [];

    /**
     * @param array<Vec2D> $route
     */
    public function __construct(
        private array $route = [],
    ) {
        $count = count($route);
        if ($count > 0) {
            $this->head = $route[array_key_last($route)];
            foreach ($route as $point) {
                $this->set($point);
            }
            $this->len = $count;
        }
    }

    public function get(): array
    {
        return $this->route;
    }

    public function count(): int
    {
        return $this->len;
    }

    public function add(Vec2D $point): bool
    {
        if ($this->head !== null) {
            $diff = $point->sub($this->head);
            if (abs($diff->x) + abs($diff->y) > 1) { return false; }
        }

        $this->route[] = $this->head = $point;
        $this->set($point);
        $this->len += 1;

        return true;
    }

    public function has(Vec2D $point): false|int
    {
        return $this->set[$point->getKey()] ?? false;
    }

    public function hasKey(string $key): false|int
    {
        return $this->set[$key] ?? false;
    }

    private function set(Vec2D $point): void
    {
        $key = $point->getKey();
        if (!$this->has($point)) {
            $this->set[$key] = 1;
        } else {
            $this->set[$key] += 1;
        }
    }
}
