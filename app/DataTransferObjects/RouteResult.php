<?php

namespace App\DataTransferObjects;

class RouteResult
{
    /**
     * @param  array<MapRoute>  $routes
     * @param  array<RouteWaypoint>  $waypoints
     */
    public function __construct(
        public array $routes,
        public array $waypoints = []
    ) {}
}
