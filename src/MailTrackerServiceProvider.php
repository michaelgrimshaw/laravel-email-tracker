<?php

namespace MichaelGrimshaw\MailTracker;

use Illuminate\Support\ServiceProvider;
use MichaelGrimshaw\MailTracker\stats\MailTrackerStats;

/**
 * Class MailTrackerServiceProvider
 *
 * @package MichaelGrimshaw\MailTracker
 */
class MailTrackerServiceProvider extends ServiceProvider
{

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/migrations');
        $this->loadRoutesFrom(__DIR__ . '/routes/routes.php');

        $this->app->alias('mail-tracker', MailTracker::class);
        $this->app->singleton('mail-tracker', function ($app) {
            return new MailTracker($app['view'], $app['swift.mailer'], $app['events']);
        });

        $this->app->alias('mail-tracker-stats', MailTracker::class);
        $this->app->singleton('mail-tracker-stats', function ($app) {
            return new MailTrackerStats();
        });

        $this->publishes([
            __DIR__.'/config/mailtracker.php' => config_path('mailtracker.php')
        ], 'config');
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {

    }
}
