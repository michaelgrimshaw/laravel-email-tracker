<?php

namespace MichaelGrimshaw\MailTracker\stats;

use stdClass;

/**
 * Class EmailStatsCollection
 *
 * @package MichaelGrimshaw\MailTracker
 */
class EmailStatsCollection
{

    /**
     * @var EmailStats
     */
    public $overview;

    /**
     * @var stdClass
     */
    public $data;

    /**
     * EmailStatsCollection constructor.
     */
    public function __construct()
    {
        $this->data = new stdClass();
    }

    /**
     * @param string $key
     *
     * @return null
     */
    public function __get($key)
    {
        if (property_exists($this->overview, $key)) {
            return $this->overview->{$key};
        }

        if (method_exists($this, $key)) {
            return $this->{$key}();
        }

        return null;
    }

    /**
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (property_exists($this->data, $method)) {
            return $this->data->{$method};
        }

        throw new \BadMethodCallException("Method $method does not exist.");
    }

    /**
     * Adds EmailStats object to the collection class.
     *
     * @param EmailStats $collection
     *
     * @return void
     */
    public function addOverview($collection)
    {
        $this->overview = $collection;
    }

    /**
     * Adds EmailStats object under a group of the collection class.
     *
     * @param string     $group
     * @param EmailStats $collection
     *
     * @return void
     */
    public function addCollectionGroup($group, $collection)
    {
        $this->data->{$group}[] = $collection;
    }

    /**
     * Converts the object values to a percent.
     *
     * @return EmailStats
     */
    public function asPercent()
    {
        return $this->overview->asPercent();
    }

    /**
     * Convert the object to its array representation.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->overview->toArray();
    }

    /**
     * Convert the object to its JSON representation.
     *
     * @param int $options
     *
     * @return string
     */
    public function toJson($options = 0)
    {
        return $this->overview->toJson($options);
    }

}