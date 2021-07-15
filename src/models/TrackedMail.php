<?php

namespace MichaelGrimshaw\MailTracker\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class TrackedMail
 *
 * @package MichaelGrimshaw\MailTracker
 */
class TrackedMail extends Model
{

    /**
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * @var string
     */
    protected $event_table;

    /**
     * @var string
     */
    protected $event_table_foreign_key;

    /**
     * TrackedMail constructor.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('mailtracker.table_names.email_tracker'));

        $this->event_table = config('mailtracker.table_names.email_tracker_event');

        $this->event_table_foreign_key = $this->getTable() . '_id';
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function recipient()
    {
        return $this->morphTo();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function linkedTo()
    {
        return $this->morphTo();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function events()
    {
        return $this->hasMany(
            config('mailtracker.models.email_tracker_event'),
            $this->event_table_foreign_key,
            'id'
        );
    }

    /**
     * Gets latest event for a mail instance
     *
     * @return Model|null|object|static
     */
    public function getLatestEvent()
    {
        return $this->hasOne(
            config('mailtracker.models.email_tracker_event'),
            $this->event_table_foreign_key)
            ->latest()
            ->first();
    }

    /**
     * Checks if a mail instance has a specific status.
     *
     * @param string $status
     *
     * @return bool
     */
    public function hasStatus($status)
    {
        return $this->events()->where('status', $status)->exists();
    }

    /**
     * Scope to get all mail with a specific status.
     *
     * @param object $query
     * @param string $status
     *
     * @return mixed
     */
    public function scopeStatus($query, $status)
    {
        return $query->join($this->event_table, function ($join) use ($status) {
            $join->on($this->getTable() . '.id', '=', $this->event_table . '.' . $this->event_table_foreign_key)
                ->where($this->event_table . '.status', '=', $status);
        });
    }

    /**
     * Scope to get mail with a specific distribution type.
     *
     * @param object $query
     * @param string $distributionType
     *
     * @return mixed
     */
    public function scopeDistributionType($query, $distributionType)
    {
        return $query->where($this->getTable() . '.distribution_type', $distributionType);
    }

    /**
     * Scope to get mail with a specific category type.
     *
     * @param object $query
     * @param string $category
     *
     * @return mixed
     */
    public function scopeCategory($query, $category)
    {
        return $query->where($this->getTable() . '.category', $category);
    }

    /**
     * Scope to get mail with a specific mail class type.
     *
     * @param object $query
     * @param string $mailClass
     *
     * @return mixed
     */
    public function scopeMailClass($query, $mailClass)
    {
        return $query->where($this->getTable() . '.mail_class', $mailClass);
    }
    
    /**
     * Scope to get all mail sent between two dates.
     *
     * @param object $query
     * @param object $startDate
     * @param object $endDate
     *
     * @return mixed
     */
    public function scopeSentBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween($this->getTable() . '.created_at', [$startDate, $endDate]);
    }

    /**
     * Deletes tracking data based on config settings.
     *
     * @return mixed
     */
    public static function clean()
    {
        if (config('mailtracker.tracking_cleaner.type') == 'limit') {
            return self::cleanByLimit(config('mailtracker.tracking_cleaner.limit'));
        }
        return self::cleanByExpiration(
            config('mailtracker.tracking_cleaner.expiration_time'),
            config('mailtracker.tracking_cleaner.expiration_unit')
        );
    }

    /**
     * Deletes tracking data passed the given limit.
     *
     * @param int $limit
     *
     * @return mixed
     */
    public static function cleanByLimit($limit = 500)
    {
        return self::orderBy('created_at', 'asc')->limit($limit)->delete();
    }

    /**
     * Deletes expired tracking data based off the time given.
     *
     * @param int    $time
     * @param string $unit
     *
     * @return mixed
     */
    public static function cleanByExpiration($time = 30, $unit = 'days')
    {
        switch ($unit) {
            case 'months':
                $expirationTime = Carbon::now()->subMonths($time);
                break;
            case 'days':
                $expirationTime = Carbon::now()->subDays($time);
                break;
            case 'hours':
                $expirationTime = Carbon::now()->subHours($time);
                break;
            case 'minutes':
                $expirationTime = Carbon::now()->subMinutes($time);
                break;
            default:
                $expirationTime = Carbon::now();
                break;
        }

        return self::where('created_at', '<', $expirationTime)->delete();
    }
}
