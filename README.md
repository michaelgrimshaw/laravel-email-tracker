# Laravel Email Tracker

This package allows you to track sent mail and query sent mail statistics.

Once installed you can do stuff like this:

```php
$user  = User::find(1);
$order = Order::find(1);
Mail::to($user)
    ->linkedTo($order)
    ->category('Order Verification')
    ->send(new testMail()));
```

By adding a trait you can access history sending history for a recipient or model. 

```php
// Get emails history sent to a user
$user->recipientHistory;
// Get emails history linked to a order
$order->mailableHistory;

```

## Installation

### Laravel

This package can be used in Laravel 5.5 or higher.

You can install the package via composer:

``` bash
composer require michaelgrimshaw/laravel-email-tracker
```

In Laravel 5.5 the service provider will automatically get registered. In older versions of the framework just add the service provider in `config/app.php` file:

```php
'providers' => [
    // ...
    MichaelGrimshaw\MailTracker\MailTrackerServiceProvider::class,
];
'aliases' => [
    // ...
    'Mail' => MichaelGrimshaw\MailTracker\Facades\Mail::class,
    'MailStats' => MichaelGrimshaw\MailTracker\Facades\MailStats::class,
];
```

You can create the mail history tables by running the migrations:

```bash
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --provider="MichaelGrimshaw\MailTracker\MailTrackerServiceProvider" --tag="config"
```

Add this line to your router file
```php

use MichaelGrimshaw\MailTracker\MailTrackerController;

Route::post('/api/email-tracker/event-hook', MailTrackerController::class . '@processEvent');
```

Or you can override MailTrackerController 
Ex:

```php

<?php

namespace App\Http\Controllers;

use MichaelGrimshaw\MailTracker\Models\TrackedMailEvent;
use MichaelGrimshaw\MailTracker\MailTrackerController as MainMailTrackerController;

class MailTrackerController extends MainMailTrackerController
{
    /**
     * Iterates over Event data.
     *
     * @return void
     */
    public function processEvent()
    {
        if (config('services.sendgrid.signing_secret') != request('secret', 'default_value')) {
            return response()->json(['error' => 'The secret validation failed'], 400);
        }

        if (empty($this->json)) {
            return response()->json(['error' => 'Empty Data'], 400);
        }

        parent::processEvent();
    }
}
```


## Usage

First, add the `MichaelGrimshaw\MailTracker\Traits\TrackableTrait` trait to your `User` model(s) and link model(s):

```php
use Illuminate\Foundation\Auth\User as Authenticatable;
use MichaelGrimshaw\MailTracker\Traits\TrackableTrait;

class User extends Authenticatable
{
    use TrackableTrait;

    // ...
}
```
### Methods

This now gives you access to extra functions when sending which can be used to control the tracking.

```php
linkedTo(object)
```

Pass in a model object to link the mail.

```php
category(string)
```
Pass a string to add a category to the tracked mail.

```php
tracked(bool)
```
As default, mail will always be tracked. You can use the tracked method to turn tracking on or off.

### Tracking Events

The default webhook url is /api/email-tracker/event-hook. You can customise the route:

```php
Route::post('custome-route', MailTrackerController::class . '@processEvent');
```

When the webhook is processed one of the following events are called:

```php
// Events
'mail.event'
'mail.processed'
'mail.dropped'
'mail.delivered'
'mail.deferred'
'mail.bounce'
'mail.open'
'mail.click'
'mail.spamreport'
'mail.group_unsubscribe'
'mail.group_resubscribe'
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security-related issues, please email [hello@michaelgrimshaw.co.uk](mailto:hello@michaelgrimshaw.co.uk) instead of using the issue tracker.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
