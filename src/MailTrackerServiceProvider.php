<?php

namespace MichaelGrimshaw\MailTracker;

use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
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
            $config = $app->make('config')->get('mail');

            // Once we have create the mailer instance, we will set a container instance
            // on the mailer. This allows us to resolve mailer classes via containers
            // for maximum testability on said classes instead of passing Closures.
            $mailer = new MailTracker(
                $app['view'], $app['swift.mailer'], $app['events']
            );

            if ($app->bound('queue')) {
                $mailer->setQueue($app['queue']);
            }

            // Next we will set all of the global addresses on this mailer, which allows
            // for easy unification of all "from" addresses as well as easy debugging
            // of sent messages since they get be sent into a single email address.
            foreach (['from', 'reply_to', 'to'] as $type) {
                $this->setGlobalAddress($mailer, $config, $type);
            }

            return $mailer;
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
     * Set a global address on the mailer by type.
     *
     * @param  \Illuminate\Mail\Mailer  $mailer
     * @param  array  $config
     * @param  string  $type
     * @return void
     */
    protected function setGlobalAddress($mailer, array $config, $type)
    {
        $address = Arr::get($config, $type);

        if (is_array($address) && isset($address['address'])) {
            $mailer->{'always'.Str::studly($type)}($address['address'], $address['name']);
        }
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
