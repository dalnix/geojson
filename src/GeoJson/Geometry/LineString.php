<?php

namespace GeoJson\Geometry;

/**
 * LineString geometry object.
 *
 * Coordinates consist of an array of at least two positions.
 *
 * @see http://www.geojson.org/geojson-spec.html#linestring
 * @since 1.0
 */
class LineString extends MultiPoint
{

    protected $type = 'LineString';

    /**
     * Constructor.
     *
     * @param float[][]|Point[] $positions
     * @param CoordinateResolutionSystem|BoundingBox $arg,...
     */
    public function __construct(array $positions)
    {
        if (count($positions) < 2) {
            throw new \InvalidArgumentException('LineString requires at least two positions');
        }

        call_user_func_array(array('parent', '__construct'), func_get_args());
    }

    public function getLength()
    {
        $length = 0;

        $coordinates = $this->coordinates;

        $i    = 0;
        $ilen = count($coordinates);

        foreach ($coordinates as $key => $coordinate) {
            if (++$i == $ilen) {
                continue;
            }

            $length += self::vincentyGreatCircleDistance($coordinates[$key][0], $coordinates[$key][1],
                $coordinates[$key + 1][0], $coordinates[$key + 1][1]);
        }

        return (float)$length;
    }

    /**
     * Calculates the great-circle distance between two points, with
     * the Vincenty formula.
     *
     * @param float $latitudeFrom Latitude of start point in [deg decimal]
     * @param float $longitudeFrom Longitude of start point in [deg decimal]
     * @param float $latitudeTo Latitude of target point in [deg decimal]
     * @param float $longitudeTo Longitude of target point in [deg decimal]
     * @param float $earthRadius Mean earth radius in [m]
     *
     * @return float Distance between points in [m] (same as earthRadius)
     */
    public static function vincentyGreatCircleDistance(
        $longitudeFrom,
        $latitudeFrom,
        $longitudeTo,
        $latitudeTo,
        $earthRadius = 6378137
    ) {
        // convert from degrees to radians
        $latFrom = deg2rad($latitudeFrom);
        $lonFrom = deg2rad($longitudeFrom);
        $latTo   = deg2rad($latitudeTo);
        $lonTo   = deg2rad($longitudeTo);

        $lonDelta = $lonTo - $lonFrom;

        $a = pow(cos($latTo) * sin($lonDelta),
                2) + pow(cos($latFrom) * sin($latTo) - sin($latFrom) * cos($latTo) * cos($lonDelta), 2);
        $b = sin($latFrom) * sin($latTo) + cos($latFrom) * cos($latTo) * cos($lonDelta);

        $angle = atan2(sqrt($a), $b);

        return $angle * $earthRadius;
    }
}
