<?php

namespace MichaelGrimshaw\MailTracker\traits;

/**
 * Trait TrackableTrait
 *
 * @package MichaelGrimshaw\MailTracker
 */
trait TrackableTrait
{

    /**
     * @return mixed
     */
    public function recipientHistory()
    {
        return $this->morphMany(config('mailtracker.models.email_tracker'), 'recipient');
    }

    /**
     * @return mixed
     */
    public function mailableHistory()
    {
        return $this->morphMany(config('mailtracker.models.email_tracker'), 'linkedTo', 'linked_to_type', 'linked_to_id');
    }
}