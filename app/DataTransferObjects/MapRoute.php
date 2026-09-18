<?php

namespace App\DataTransferObjects;

class MapRoute
{
    /**
     * @param  string|mixed[]  $geometry
     */
    public function __construct(
        public string|array $geometry,
        public float $distance,
        public float $duration
    ) {}
}
