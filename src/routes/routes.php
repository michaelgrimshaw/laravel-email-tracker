<?php

use MichaelGrimshaw\MailTracker\MailTrackerController;

Route::post('/api/email-tracker/event-hook', MailTrackerController::class . '@processEvent');