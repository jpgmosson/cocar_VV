<?php

namespace App\Casts;

use App\ValueObjects\Point;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Facades\DB;

class PointCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if (! $value) {
            return null;
        }

        if ($value instanceof Point) {
            return $value;
        }

        if ($value instanceof Expression) {
            $value = $value->getValue(DB::connection()->getQueryGrammar());
        }

        $geo = is_array($value) ? $value : json_decode($value, true);
        if (is_array($geo) && isset($geo['coordinates'])) {
            return new Point($geo['coordinates'][0], $geo['coordinates'][1]);
        }

        if (is_string($value) && preg_match('/POINT\s*\(\s*([-\d.]+)\s+([-\d.]+)\s*\)/i', $value, $matches)) {
            return new Point((float) $matches[1], (float) $matches[2]);
        }

        if (is_string($value) && ctype_xdigit($value) && strlen($value) >= 42) {
            $binary = hex2bin($value);
            $unpacked = unpack('x/x4/x4/dlat/dlng', $binary);
            if ($unpacked) {
                return new Point($unpacked['lat'], $unpacked['lng']);
            }
        }

        return null;
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if (! $value) {
            return null;
        }

        if ($value instanceof Expression) {
            return $value;
        }

        if (! $value instanceof Point) {
            $value = Point::from($value);
        }

        return DB::raw("ST_GeomFromText('POINT({$value->x} {$value->y})', 4326)");
    }
}
