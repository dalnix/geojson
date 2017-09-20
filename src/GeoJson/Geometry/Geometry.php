<?php

namespace GeoJson\Geometry;

use GeoJson\GeoJson;

/**
 * Base geometry object.
 *
 * @see http://www.geojson.org/geojson-spec.html#geometry-objects
 * @since 1.0
 */
abstract class Geometry extends GeoJson
{

    /**
     * @var array
     */
    protected $coordinates;

    /**
     * Return the coordinates for this Geometry object.
     *
     * @return array
     */
    public function getCoordinates()
    {
        return $this->coordinates;
    }

    /**
     * @see http://php.net/manual/en/jsonserializable.jsonserialize.php
     */
    public function jsonSerialize()
    {
        $json = parent::jsonSerialize();

        if (isset($this->coordinates)) {
            $json['coordinates'] = $this->coordinates;
        }

        return $json;
    }

    /**
     * Get center coordinates of Geometry
     *
     * @return Point
     */
    public function getCenter()
    {
        $coordinates = $this->getCoordinates()[0];

        $max_lat = $min_lat = $coordinates[0][1];
        $max_lon = $min_lon = $coordinates[0][0];

        foreach ($coordinates as $coordinate) {
            if ($coordinate[1] > $max_lat) {
                $max_lat = $coordinate[1];
            }
            if ($coordinate[1] < $min_lat) {
                $min_lat = $coordinate[1];
            }
            if ($coordinate[0] > $max_lon) {
                $max_lon = $coordinate[0];
            }
            if ($coordinate[0] < $min_lon) {
                $min_lon = $coordinate[0];
            }
        }

        $lon = ($min_lon + $max_lon) / 2;
        $lat = ($min_lat + $max_lat) / 2;

        return new Point([$lon, $lat]);
    }

}
