<?php

namespace App\ValueObjects;

use InvalidArgumentException;
use JsonSerializable;

class Point implements JsonSerializable
{
    public function __construct(
        public float $x,
        public float $y
    ) {}

    public static function from(mixed $value): self
    {
        if (is_array($value)) {
            return new self((float) $value[0], (float) $value[1]);
        }

        if (is_string($value)) {
            [$x, $y] = explode(',', $value);

            return new self((float) $x, (float) $y);
        }

        throw new InvalidArgumentException('Invalid point data structure');
    }

    public static function formatPoints(self ...$points): string
    {
        $mapped = array_map(
            fn ($p) => "{$p->x},{$p->y}",
            $points
        );

        return implode(';', $mapped);
    }

    public function jsonSerialize(): array
    {
        return [$this->x, $this->y];
    }
}
