<?php

namespace App\Providers;

// Events
use Illuminate\Auth\Events\Registered;

// Listeners
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'App\Events\InvoiceCreationPreEmail' => [
           'App\Hooks\Registervanewclientcheckout',
       ],
    //    Registered::class => [
    //         SendEmailVerificationNotification::class,
    //     ],
    //     InvoicePaid::class => [
    //         InvoicePaidHook::class,
    //     ],
        Registered::class => [
            // SendEmailVerificationNotification::class,
        ],
        'Illuminate\Auth\Events\Verified' => [
            'App\Listeners\UpdateVerifiedEmailClient',
        ],
        'Illuminate\Auth\Events\Login' => [
            'App\Listeners\UpdateLastLoginClient',
        ],
        'Illuminate\Auth\Events\Logout' => [
            'App\Listeners\ClientLoggedOut',
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        // Atau bisa juga didaftarkan seperti ini
        // Event::listen(InvoicePaid::class, function ($event) {
        //     $hook = new InvoicePaidHook();
        //     $hook->handle($event);
        // });
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return true;
    }

    /**
     * Get the listener directories that should be used to discover events.
     *
     * @return array
     */
    protected function discoverEventsWithin()
    {
        return [
            $this->app->path('Hooks'),
        ];
    }
}
