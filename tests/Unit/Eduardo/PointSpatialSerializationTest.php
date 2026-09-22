<?php

namespace Tests\Unit\Eduardo;

use App\Casts\PointCast;
use App\Models\Trajeto;
use App\ValueObjects\Point;
use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Grammars\PostgresGrammar;
use InvalidArgumentException;
use Tests\TestCase;

class PointSpatialSerializationTest extends TestCase
{
    public function test_point_from_mapeia_x_como_longitude_e_y_como_latitude(): void
    {
        $pointStr = Point::from('-49.2733,-25.4284');
        $this->assertEquals(-49.2733, $pointStr->x);
        $this->assertEquals(-25.4284, $pointStr->y);

        $pointArr = Point::from([-49.2733, -25.4284]);
        $this->assertEquals(-49.2733, $pointArr->x);
        $this->assertEquals(-25.4284, $pointArr->y);

        $this->assertEquals('[-49.2733,-25.4284]', json_encode($pointStr));
    }

    public function test_point_lanca_excecao_para_formato_invalido(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Point::from(12345);
    }

    public function test_point_cast_set_gera_expressao_postgis_correta(): void
    {
        $cast = new PointCast;
        $model = new Trajeto;

        /** @var Expression $rawSql */
        $rawSql = $cast->set($model, 'origem_coords', new Point(-49.2733, -25.4284), []);

        $connection = \Mockery::mock(Connection::class);
        $grammar = new PostgresGrammar($connection);

        $this->assertInstanceOf(Expression::class, $rawSql);
        $this->assertEquals(
            "ST_GeomFromText('POINT(-49.2733 -25.4284)', 4326)",
            $rawSql->getValue($grammar)
        );
    }

    public function test_point_cast_get_hidrata_geojson_string(): void
    {
        $cast = new PointCast;
        $model = new Trajeto;

        $geojson = json_encode(['coordinates' => [-49.2733, -25.4284]]);
        $point = $cast->get($model, 'origem_coords', $geojson, []);

        $this->assertInstanceOf(Point::class, $point);
        $this->assertEquals(-49.2733, $point->x);
        $this->assertEquals(-25.4284, $point->y);
    }
}
