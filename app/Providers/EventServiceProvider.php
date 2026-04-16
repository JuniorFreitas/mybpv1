<?php

namespace App\Providers;

use App\Listeners\RedirectLocalMailCcAndBcc;
use App\Listeners\SuppressConfiguredMailRecipients;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Mail\Events\MessageSending;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        // RedirectLocal (local + MAIL_MAILER=ses) deve vir antes: MessageSending usa `until()` e para no primeiro retorno não nulo (ex.: false do Suppress).
        MessageSending::class => [
            RedirectLocalMailCcAndBcc::class,
            SuppressConfiguredMailRecipients::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
