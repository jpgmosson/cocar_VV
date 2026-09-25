<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Facades\DB;

class GeoJSONCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($value instanceof Expression) {
            return null;
        }

        if (is_array($value)) {
            return $value;
        }

        return is_string($value) ? json_decode($value, true) : null;
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if (! $value) {
            return null;
        }

        if ($value instanceof Expression) {
            return $value;
        }

        if (is_array($value)) {
            $value = json_encode($value);
        }

        return DB::raw("ST_GeomFromGeoJSON('{$value}')::geography");
    }
}
