<?php

namespace MichaelGrimshaw\MailTracker;

use Carbon\Carbon;

/**
 * Class MailTrackerStats
 *
 * @package MichaelGrimshaw\MailTracker
 */
class MailTrackerStats
{

    /**
     * @var array
     */
    protected $period = [];

    /**
     * @var EmailStatsCollection
     */
    protected $collection;

    /**
     * @var object
     */
    protected $query;

    /**
     * @var array
     */
    protected $where = [];

    /**
     * MailTrackerStats constructor.
     */
    public function __construct()
    {
        $this->period = [
            'from'   => Carbon::now()->subDay(),
            'to' => Carbon::now()
        ];

        $this->collection = new EmailStatsCollection();
    }

    /**
     * Sets custom period of dates using string or carbon objects.
     *
     * @param mixed $from
     * @param mixed $to
     *
     * @return MailTrackerStats
     */
    public function period($from, $to)
    {
        $from = $this->makeCarbon($from);
        $to   = $this->makeCarbon($to);

        return $this->setPeriod($from, $to);
    }

    /**
     * Sets period for the last 24 hours.
     *
     * @return MailTrackerStats
     */
    public function past24Hours()
    {
        return $this->setPeriod(Carbon::now()->subDay(), Carbon::now());
    }

    /**
     * Sets period for today.
     *
     * @return MailTrackerStats
     */
    public function today()
    {
        return $this->setPeriod(Carbon::now()->startOfDay(), Carbon::now()->endOfDay());
    }

    /**
     * Sets period for yesterday.
     *
     * @return MailTrackerStats
     */
    public function yesterday()
    {
        return $this->setPeriod(Carbon::now()->subDay()->startOfDay(), Carbon::now()->subDay()->endOfDay());
    }

    /**
     * Sets period for the last 7 days.
     *
     * @return MailTrackerStats
     */
    public function past7Days()
    {
        return $this->setPeriod(Carbon::now()->subDays(7)->startOfDay(), Carbon::now()->endOfDay());
    }

    /**
     * Sets period from the beginning til the end of this week.
     *
     * @return MailTrackerStats
     */
    public function thisWeek()
    {
        return $this->setPeriod(Carbon::now()->startOfWeek()->startOfDay(), Carbon::now()->endOfWeek()->endOfDay());
    }

    /**
     * Sets period from the beginning til the end of last week.
     *
     * @return MailTrackerStats
     */
    public function pastWeek()
    {
        return $this->setPeriod(Carbon::now()->subWeek()->startOfWeek()->startOfDay(), Carbon::now()->subWeek()->endOfWeek()->endOfDay());
    }

    /**
     * Sets period for the last 30 days.
     *
     * @return MailTrackerStats
     */
    public function past30Days()
    {
        return $this->setPeriod(Carbon::now()->subDays(30)->startOfDay(), Carbon::now()->endOfDay());
    }

    /**
     * Sets period from the beginning til the end of this month.
     *
     * @return MailTrackerStats
     */
    public function thisMonth()
    {
        return $this->setPeriod(Carbon::now()->startOfMonth()->startOfDay(), Carbon::now()->endOfMonth()->endOfDay());
    }

    /**
     * Sets period from the beginning til the end of last month.
     *
     * @return MailTrackerStats
     */
    public function pastMonth()
    {
        return $this->setPeriod(Carbon::now()->subMonth()->startOfMonth()->startOfDay(), Carbon::now()->subMonth()->endOfMonth()->endOfDay());
    }

    /**
     * Sets period for the last 365 days.
     *
     * @return MailTrackerStats
     */
    public function past365Days()
    {
        return $this->setPeriod(Carbon::now()->subDays(365)->startOfDay(), Carbon::now()->endOfDay());
    }

    /**
     * Sets period from the beginning til the end of this year.
     *
     * @return MailTrackerStats
     */
    public function thisYear()
    {
        return $this->setPeriod(Carbon::now()->startOfYear()->startOfDay(), Carbon::now()->endOfYear()->endOfDay());
    }

    /**
     * Sets period from the beginning til the end of last year.
     *
     * @return MailTrackerStats
     */
    public function PastYear()
    {
        return $this->setPeriod(Carbon::now()->subYear()->startOfYear()->startOfDay(), Carbon::now()->subYear()->endOfYear()->endOfDay());
    }

    /**
     * Filter by emails.
     *
     * @param string|array $data
     *
     * @return MailTrackerStats
     */
    public function to($data)
    {
        $this->setWhere(config('mailtracker.table_names.email_tracker') . '.email', '=', $data);

        return $this;
    }

    /**
     * Filter by categories.
     *
     * @param string|array $data
     *
     * @return MailTrackerStats
     */
    public function withCategory($data)
    {
        $this->setWhere(config('mailtracker.table_names.email_tracker') . '.category', '=', $data);

        return $this;
    }

    /**
     * Filter by mail class.
     *
     * @param string|array $data
     *
     * @return MailTrackerStats
     */
    public function withMailClass($data)
    {
        $this->setWhere(config('mailtracker.table_names.email_tracker') . '.mail_class', '=', $data);

        return $this;
    }

    /**
     * Filter by distribution type.
     *
     * @param string|array $data
     *
     * @return MailTrackerStats
     */
    public function withSendType($data)
    {
        $this->setWhere(config('mailtracker.table_names.email_tracker') . '.distribution_type', '=', $data);

        return $this;
    }

    /**
     * Filter by event.
     *
     * @param string|array $data
     *
     * @return MailTrackerStats
     */
    public function withEvent($data)
    {
        $this->setWhere(config('mailtracker.table_names.email_tracker_event') . '.status', '=', $data);

        return $this;
    }

    /**
     * Gets data and groups in days, months and years.
     *
     * @return EmailStatsCollection
     */
    public function get()
    {
        $this->setQuery();

        $emails = $this->query->get();

        $this->processTotal($emails);
        $this->processDays($emails);
        $this->processMonths($emails);
        $this->processYears($emails);

        return $this->collection;
    }

    /**
     * Gets data and group in only days.
     *
     * @return EmailStatsCollection
     */
    public function getDays()
    {
        $this->setQuery();

        $emails = $this->query->get();

        $this->processTotal($emails);
        $this->processDays($emails);

        return $this->collection;
    }

    /**
     * Gets data and group in only months.
     *
     * @return EmailStatsCollection
     */
    public function getMonths()
    {
        $this->setQuery();

        $emails = $this->query->get();

        $this->processTotal($emails);
        $this->processMonths($emails);

        return $this->collection;
    }

    /**
     * Gets data and group in only Years.
     *
     * @return EmailStatsCollection
     */
    public function getYears()
    {
        $this->setQuery();

        $emails = $this->query->get();

        $this->processTotal($emails);
        $this->processYears($emails);

        return $this->collection;
    }

    /**
     * Processes total data.
     *
     * @param object $emails
     *
     * @return void
     */
    private function processTotal($emails)
    {
        $data = [];

        foreach ($emails as $email) {
            if (is_null($email->queue)) {
                $email->queue = 'not-queued';
            }

            $this->increment($data, 'total');
            $this->increment($data, 'emails', $email->email);
            $this->increment($data, 'categories', $email->category);
            $this->increment($data, 'mail_classes', $email->mail_class);
            $this->increment($data, 'queues', $email->queue);

            foreach ($email->events()->get() as $event) {
                $this->increment($data, 'events', $event->status);
            }
        }

        $this->collection->addOverview(new EmailStats($data));
    }

    /**
     * Processes data for Days.
     *
     * @param object $emails
     *
     * @return void
     */
    private function processDays($emails)
    {
        $this->processData($emails, 'days', 'Y-m-d');
    }

    /**
     * Processes data for Months.
     *
     * @param object $emails
     *
     * @return void
     */
    private function processMonths($emails)
    {
        $this->processData($emails, 'months', 'Y-m');
    }

    /**
     * Processes data for Years.
     *
     * @param object $emails
     *
     * @return void
     */
    private function processYears($emails)
    {
        $this->processData($emails, 'years', 'Y');
    }

    /**
     * @param object $emails
     * @param string $group
     * @param string $dateFormat
     *
     * @return void
     */
    private function processData($emails, $group, $dateFormat)
    {
        $data = [];

        foreach ($emails as $email) {
            if (is_null($email->queue)) {
                $email->queue = 'not-queued';
            }

            $this->increment($data, 'total', null, $email->created_at->format($dateFormat));
            $this->increment($data, 'emails', $email->email, $email->created_at->format($dateFormat));
            $this->increment($data, 'categories', $email->category, $email->created_at->format($dateFormat));
            $this->increment($data, 'mail_classes', $email->mail_class, $email->created_at->format($dateFormat));
            $this->increment($data, 'queues', $email->queue, $email->created_at->format($dateFormat));

            foreach ($email->events()->get() as $event) {
                $this->increment($data, 'events', $event->status, $email->created_at->format($dateFormat));
            }
        }

        foreach ($data as $period => $datum) {
            $this->collection->addCollectionGroup($group, new EmailStats($datum, $period));
        }
    }

    /**
     * Increments data.
     *
     * @param array       $data
     * @param string      $type
     * @param string|null $key
     * @param string|null $date
     *
     * @return void
     */
    private function increment(&$data, $type, $key = null, $date = null)
    {
        if (!is_null($key) && !is_null($date)){
            $this->incrementArray($data[$date][$type][$key]);
        } elseif (!is_null($key) && is_null($date)) {
            $this->incrementArray($data[$type][$key]);
        } elseif (is_null($key) && !is_null($date)) {
            $this->incrementArray($data[$date][$type]);
        } elseif (is_null($key) && is_null($date)) {
            $this->incrementArray($data[$type]);
        }
    }

    /**
     * Checks and increments array item.
     *
     * @param mixed $array
     *
     * @return void
     */
    private function incrementArray(&$array)
    {
        if (!isset($array)) {
            $array = 0;
        }
        $array++;
    }

    /**
     * Adds where clause data to array.
     *
     * @param string $column
     * @param string $operator
     * @param string $data
     *
     * @return void
     */
    private function setWhere($column, $operator, $data)
    {
        if (is_array($data)) {
            foreach ($data as $value) {
                $this->where[] = [
                    'column'   => $column,
                    'operator' => $operator,
                    'value'    => $value
                ];
            }
        } else {
            $this->where[] = [
                'column'   => $column,
                'operator' => $operator,
                'value'    => $data
            ];
        }
    }

    /**
     * Builds query.
     *
     * @return void
     */
    private function setQuery()
    {
        $this->query = TrackedMail::with('events')
            ->select(config('mailtracker.table_names.email_tracker') . '.*')
            ->leftJoin(
                config('mailtracker.table_names.email_tracker_event'),
                config('mailtracker.table_names.email_tracker') . '.id',
                '=',
                config('mailtracker.table_names.email_tracker_event') . '.' . config('mailtracker.table_names.email_tracker') . '_id'
            )
            ->whereBetween(config('mailtracker.table_names.email_tracker') . '.created_at', $this->period);

        if (!empty($this->where)) {
            foreach ($this->where as $where) {
                $this->query->where($where['column'], $where['operator'], $where['value']);
            }
        }

        $this->query->groupBy(config('mailtracker.table_names.email_tracker') . '.id');
    }

    /**
     * Checks to see if value is of Carbon\Carbon and creates one if not.
     *
     * @param string|object $value
     *
     * @return object
     */
    private function makeCarbon($value)
    {
        if (!$value instanceof Carbon) {
            $value = Carbon::parse($value);
        }

        return $value;
    }

    /**
     * Sets Carbon to period array.
     *
     * @param object $from
     * @param object $to
     *
     * @return $this
     */
    private function setPeriod($from, $to)
    {
        $this->period = [
            'from' => $from,
            'to'   => $to
        ];

        return $this;
    }
}
