<?php

namespace MichaelGrimshaw\MailTracker\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class MailStats
 *
 * @package MichaelGrimshaw\MailTracker\Facades
 * @see \MichaelGrimshaw\MailTracker\MailTracker
 */
class MailStats extends Facade
{

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'mail-tracker-stats';
    }
}
