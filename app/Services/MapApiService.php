<?php

namespace App\Services;

use App\DataTransferObjects\MapRoute;
use App\DataTransferObjects\RouteResult;
use App\DataTransferObjects\RouteWaypoint;
use App\ValueObjects\Point;
use Illuminate\Support\Facades\Http;

class MapApiService
{
    public function obterRotaDireta(Point $origem, Point $destino): RouteResult
    {
        $res = Http::mapbox()->get(
            '/directions/v5/mapbox/driving-traffic/'.Point::formatPoints($origem, $destino),
            ['alternatives' => 'true']
        );
        $data = $res->json();

        $routes = collect($data['routes'] ?? [])
            ->map(fn ($r) => new MapRoute(
                geometry: $r['geometry'],
                distance: (float) $r['distance'],
                duration: (float) $r['duration']
            ))
            ->toArray();

        return new RouteResult($routes);
    }

    /**
     * @param  array<Point>  $paradas
     */
    public function obterRotaOtimizada(array $paradas): RouteResult
    {
        $path = 'optimized-trips/v1/mapbox/driving-traffic/'.Point::formatPoints(...$paradas);
        $res = Http::mapbox()->get($path, ['roundtrip' => 'false', 'source' => 'first', 'destination' => 'last']);
        $data = $res->json();

        $routes = collect($data['trips'] ?? [])
            ->map(fn ($t) => new MapRoute(
                geometry: $t['geometry'],
                distance: (float) $t['distance'],
                duration: (float) $t['duration']
            ))
            ->toArray();

        $waypoints = collect($data['waypoints'] ?? [])
            ->map(fn ($w) => new RouteWaypoint(
                index: $w['waypoint_index'],
                longitude: $w['location'][0],
                latitude: $w['location'][1]
            ))
            ->toArray();

        return new RouteResult($routes, $waypoints);
    }
}
