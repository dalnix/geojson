<?php

namespace GeoJson\Feature;

use GeoJson\GeoJson;

/**
 * Collection of Feature objects.
 *
 * @see http://www.geojson.org/geojson-spec.html#feature-collection-objects
 * @since 1.0
 */
class FeatureCollection extends GeoJson implements \Countable, \IteratorAggregate
{

    protected $type = 'FeatureCollection';

    /**
     * @var array
     */
    protected $features;

    /**
     * Constructor.
     *
     * @param Feature[] $features
     * @param CoordinateResolutionSystem|BoundingBox $arg,...
     */
    public function __construct(array $features)
    {
        foreach ($features as $feature) {
            if ( ! $feature instanceof Feature) {
                throw new \InvalidArgumentException('FeatureCollection may only contain Feature objects');
            }
        }

        $this->features = array_values($features);

        if (func_num_args() > 1) {
            $this->setOptionalConstructorArgs(array_slice(func_get_args(), 1));
        }

        return $this;
    }

    /**
     * @param Feature $feature
     *
     * @return $this
     */
    public function addFeature(Feature $feature)
    {
        $this->features[] = $feature;

        return $this;
    }

    /**
     * @param int $index
     *
     * @return $this
     */
    public function removeFeatureAt(int $index)
    {
        array_splice($this->features, $index, 1);

        return $this;
    }

    /**
     * @see http://php.net/manual/en/countable.count.php
     */
    public function count()
    {
        return count($this->features);
    }

    /**
     * Return the Feature objects in this collection with optional filter.
     *
     * @param array $filter
     *
     * @return array
     */
    public function getFeatures($filter = [])
    {
        if ($filter) {
            $features = [];
            foreach ($filter as $key => $val) {
                if (is_array($val)) {
                    // @todo
                } else {
                    foreach ($this->features as $feature) {
                        $properties = $feature->getProperties();

                        if (count(array_intersect($properties, $filter)) == count($filter)) {
                            if ( ! in_array($feature, $features)) {
                                $features[] = $feature;
                            }
                        }

                    }
                }
            }
        } else {
            $features = $this->features;
        }

        return $features;
    }


    /**
     * $objects = [
     * [
     * 'type' => 'point',
     * 'color' => '#555555',
     * 'status' => 'done'
     *
     * ],
     * [
     * 'type' => 'point',
     * 'color' => '#111111',
     * ],
     * [
     * 'type' => 'area',
     * 'color' => '#555555',
     * ],
     * [
     * 'type' => 'area',
     * 'color' => '#111111',
     * 'status' => 'done'
     * ]
     * ];
     * */

    public function is_multi($a)
    {
        $rv = array_filter($a, 'is_array');
        if (count($rv) > 0) {
            return true;
        }

        return false;
    }

    public function dalnixFilter($array, $filters, $resultingObjects = [])
    {
        foreach ($filters as $key => $filter) {
            if ( ! $this->is_multi($filters)) {
                foreach ($array as $object) {
                    $result = array_intersect($object, $filters);
                    if (count($result) == count($filters)) {
                        if ( ! in_array($object, $resultingObjects)) {
                            array_push($resultingObjects, $object);
                        }
                    }
                }
            } else {
                if ( ! is_array($filter)) {
                    continue;
                }
                foreach ($filter as $property) {
                    $newFilters       = $filters;
                    $newFilters[$key] = $property;

                    $newObjects = $this->dalnixFilter($array, $newFilters, $resultingObjects);
                    foreach ($newObjects as $newObject) {
                        if ( ! in_array($newObject, $resultingObjects)) {
                            array_push($resultingObjects, $newObject);
                        }
                    }
                }
            }
        }

        return $resultingObjects;
    }


    /**
     * @see http://php.net/manual/en/iteratoraggregate.getiterator.php
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->features);
    }

    /**
     * @see http://php.net/manual/en/jsonserializable.jsonserialize.php
     */
    public function jsonSerialize()
    {
        return array_merge(
            parent::jsonSerialize(),
            array(
                'features' => array_map(
                    function (Feature $feature) {
                        return $feature->jsonSerialize();
                    },
                    $this->features
                )
            )
        );
    }

    public function __toString()
    {
        return json_encode($this);
    }
}
