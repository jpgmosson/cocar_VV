<?php

namespace App\DataTransferObjects;

class RouteWaypoint
{
    public function __construct(
        public int $index,
        public float $longitude,
        public float $latitude
    ) {}
}
