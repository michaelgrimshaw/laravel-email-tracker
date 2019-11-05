<?php

return [

    /*
     * This is a list of keys which will be removed
     * from the event data before storing the event data.
     */

    'dont_keep_tracking_data' => [
        'ip',
        'email',
    ],

    /*
     * To set the distribution types you want to track.
     */

    'track_distribution_type' => [

        /*
         * By default, the distribution type of "to" is enabled.
         * This allows us to know if we should track mail sent through the to function.
         */

        'to' => true,

        /*
         * By default, the distribution type of "cc" is enabled.
         * This allows us to know if we should track mail sent through the cc function.
         */

        'cc' => true,

        /*
         * By default, the distribution type of "bcc" is disabled.
         * This allows us to know if we should track mail sent through the bcc function.
         */

        'bcc' => false,
    ],

    /*
     * Tracking Cleaner is a feature to remove old tracking data. This can be
     * controlled by either a limit of mail stored in the database or by
     * an expiration time of all tracking data.
     */

    'tracking_cleaner' => [

        /*
         * By default, removing old tracker data is disabled.
         * When enabled this will remove old data based on the type
         * and type data set.
         */

        'enabled' => false,

        /*
         * This is the type of cleaning used if the tracking cleaner is enabled.
         * You can set this to be either "limit" or "expiration".
         */

        'type' => 'limit',

        /*
         * If the type of "limit" is set, We will use the limit value to know how many
         * tracked mails to be stored before deleting old tracking data.
         */

        'limit' => 500,

        /*
         * If the type of "expiration" is set, we will use the expiration time to
         * check if a tracked mail is expired. The unit of measure used is set in the expiration unit.
         */

        'expiration_time' => 30,

        /*
         * By default the unit used for expiration time is day.
         * You can set this to one of the following: months, days, hours, minutes.
         */

        'expiration_unit' => 'days'

    ],

    'models' => [

        /*
         * When tracking mail from this package, we need to know which
         * Eloquent model should be used to store and retrieve sent mail. Of course, it
         * is often just the "TrackedMail" model but you may use whatever you like.
         */

        'email_tracker' => MichaelGrimshaw\MailTracker\TrackedMail::class,

        /*
         * When tracking mail from this package, we need to know which
         * Eloquent model should be used to store and retrieve mail events. Of course, it
         * is often just the "TrackedMail" model but you may use whatever you like.
         */

        'email_tracker_event' => MichaelGrimshaw\MailTracker\TrackedMailEvent::class,

    ],

    'table_names' => [

        /*
         * When tracking mail from this package, we need to know which
         * table should be used to store and retrieve sent mail. We have chosen a basic
         * default value but you may easily change it to any table you like.
         */

        'email_tracker' => 'email_tracker',

        /*
         * When tracking mail from this package, we need to know which
         * table should be used to store and retrieve mail events. We have chosen a basic
         * default value but you may easily change it to any table you like.
         */

        'email_tracker_event' => 'email_tracker_event'

    ],
    
    
    /*
     * When Tracking mail the following string will be replaced with the
     * tracking id in the body of the email.
     */
    'tracker_id_replacement' => '##tracker_id##',
    
    'header_details' => [
        'api'    => 'X-SMTPAPI',
        'header' => 'unique_args'
    ],

];
