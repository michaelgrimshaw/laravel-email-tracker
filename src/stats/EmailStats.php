<?php

namespace MichaelGrimshaw\MailTracker\stats;

/**
 * Class EmailStats
 *
 * @package MichaelGrimshaw\MailTracker
 */
class EmailStats
{

    /**
     * @var int
     */
    public $total = 0;

    /**
     * @var string|null
     */
    public $period = null;

    /**
     * @var array
     */
    public $events = [];

    /**
     * @var array
     */
    public $emails = [];

    /**
     * @var array
     */
    public $categories = [];

    /**
     * @var array
     */
    public $mail_classes = [];

    /**
     * @var array
     */
    public $queues = [];

    /**
     * EmailStats constructor.
     *
     * @param $data
     * @param null $period
     */
    public function __construct($data, $period = null)
    {
        $this->period = $period;

        foreach ($data as $key => $value) {
            $this->{$key} = $value;
        }
    }

    /**
     * Converts the object values to a percent.
     *
     * @return EmailStats
     */
    public function asPercent()
    {
        $percent = [];

        foreach ($this->toArray() as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $arrayKey => $arrayValue) {
                    $percent[$key][$arrayKey] = number_format(($arrayValue / $this->total) * 100, 2);
                }
            } else {
                $percent[$key] = $value;
            }
        }

        return new EmailStats($percent, $this->period);
    }

    /**
     * Convert the object to its array representation.
     *
     * @return array
     */
    public function toArray()
    {
        return (array) $this;
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
        return json_encode($this, $options);
    }
}