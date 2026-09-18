<?php

namespace App\Casts;

use App\ValueObjects\Point;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * @implements CastsAttributes<mixed,mixed>
 */
class PointCast implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {

        if (! $value) {
            return null;
        }

        if ($value instanceof Point) {
            return $value;
        }

        $geo = json_decode($value, true);

        if (! isset($geo['coordinates'])) {
            return null;
        }

        return new Point($geo['coordinates'][0], $geo['coordinates'][1]);
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if (! $value) {
            return null;
        }

        if (! $value instanceof Point) {
            $value = Point::from($value);
        }

        return DB::raw("ST_GeomFromText('POINT({$value->x} {$value->y})', 4326)");
    }
}
