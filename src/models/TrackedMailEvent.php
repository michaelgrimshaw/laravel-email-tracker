<?php

namespace MichaelGrimshaw\MailTracker;

use Illuminate\Database\Eloquent\Model;

/**
 * Class TrackedMailEvent
 *
 * @package MichaelGrimshaw\MailTracker
 */
class TrackedMailEvent extends Model
{

    const DEFAULT_EVENT           = 'mail.event';
    const PROCESSED_EVENT         = 'mail.processed';
    const DROPPED_EVENT           = 'mail.dropped';
    const DELIVERED_EVENT         = 'mail.delivered';
    const DEFERRED_EVENT          = 'mail.deferred';
    const BOUNCE_EVENT            = 'mail.bounce';
    const OPEN_EVENT              = 'mail.open';
    const CLICK_EVENT             = 'mail.click';
    const SPAM_REPORT_EVENT       = 'mail.spamreport';
    const UNSUBSCRIBE_EVENT       = 'mail.unsubscribe';
    const GROUP_UNSUBSCRIBE_EVENT = 'mail.group_unsubscribe';
    const GROUP_RESUBSCRIBE_EVENT = 'mail.group_resubscribe';

    /**
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * @var string
     */
    protected $mail_table;

    /**
     * @var string
     */
    protected $foreign_key;

    /**
     * @var array
     */
    protected $casts = [
        'event_data' => 'array',
    ];


    /**
     * TrackedMailEvent constructor.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('mailtracker.table_names.email_tracker_event'));

        $this->mail_table = config('mailtracker.table_names.email_tracker');

        $this->foreign_key = $this->mail_table . '_id';
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function mail()
    {
       return $this->belongsTo(config('mailtracker.models.email_tracker'), $this->foreign_key);
    }
}
